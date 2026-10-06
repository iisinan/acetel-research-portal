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
        } elseif ($user->hasAnyRole(['Supervisor', 'Program Coordinator', 'Internal Examiner', 'External Examiner'])) {
            return $this->supervisorDashboard($user, $data);
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
        $isSupervisor = $user->hasRole('Supervisor');
        $isCoordinator = $user->hasRole('Program Coordinator');
        $isInternalExaminer = $user->hasRole('Internal Examiner');
        $isExternalExaminer = $user->hasRole('External Examiner');

        $activeFacultyRoles = [
            'supervisor' => $isSupervisor,
            'coordinator' => $isCoordinator,
            'internal_examiner' => $isInternalExaminer,
            'external_examiner' => $isExternalExaminer,
        ];
        $facultyRoleCount = count(array_filter($activeFacultyRoles));
        $data['activeFacultyRoles'] = $activeFacultyRoles;
        $data['facultyRoleCount'] = $facultyRoleCount;

        // Determine default tab based on roles
        if ($facultyRoleCount > 1) {
            $data['defaultTab'] = 'overview';
        } elseif ($isSupervisor) {
            $data['defaultTab'] = 'supervision';
        } elseif ($isCoordinator) {
            $data['defaultTab'] = 'coordination';
        } elseif ($isInternalExaminer) {
            $data['defaultTab'] = 'internal_exam';
        } elseif ($isExternalExaminer) {
            $data['defaultTab'] = 'external_exam';
        } else {
            $data['defaultTab'] = 'overview';
        }

        // 1. SUPERVISION TELEMETRY
        $supervisor = null;
        $supervisorStudents = collect();
        $supervisorAssignments = collect();
        $pendingSupervisorReviews = collect();
        $pendingSeminars = collect();

        $supervisionAnalytics = [
            'total_students' => 0,
            'average_progress' => 0,
            'action_needed_count' => 0,
            'stalled_count' => 0,
            'stage_proposal' => 0,
            'stage_research' => 0,
            'stage_defense' => 0,
        ];

        if ($isSupervisor) {
            $supervisor = SupervisorProfile::where('user_id', $user->id)
                ->with([
                    'assignments.thesis.student.user', 
                    'assignments.thesis.student.program', 
                    'assignments.thesis.student.level',
                    'assignments.thesis.milestones.template', 
                    'assignments.thesis.milestones.submissions.feedbacks',
                    'programs'
                ])
                ->first();
            
            $supervisorAssignments = $supervisor ? $supervisor->assignments : collect();
            
            $pendingSupervisorMilestoneThesisIds = [];
            if ($supervisor) {
                $pendingSupervisorReviews = \App\Models\StudentMilestone::whereHas('thesis.assignments', function($q) use ($supervisor) {
                        $q->where('supervisor_profile_id', $supervisor->id)->where('status', 'active');
                    })
                    ->where('status', '!=', 'approved')
                    ->whereHas('template', function($q) {
                        $q->whereJsonContains('required_approvers', 'Supervisor');
                    })
                    ->whereNotNull('submitted_at')
                    ->with(['thesis.student.user', 'template', 'thesis.assignments.supervisor.user', 'submissions.feedbacks'])
                    ->latest()
                    ->get()
                    ->filter(function($milestone) use ($user) {
                        return $this->isMilestonePendingReviewForUser($milestone, $user->id, 'Supervisor');
                    })
                    ->values();

                $pendingSupervisorMilestoneThesisIds = $pendingSupervisorReviews->pluck('thesis_project_id')->unique()->toArray();
            }

            $supervisorStudents = $supervisorAssignments->map(function($a) use ($pendingSupervisorMilestoneThesisIds) {
                $s = $a->thesis?->student;
                if ($s && $a->thesis) {
                    $s->overall_progress = $a->thesis->progress_percentage;
                    $s->thesis_title = $a->thesis->title ?? 'Postgraduate Thesis Investigation';
                    $s->assignment_role = $a->role ?? 'Supervisor';
                    $currentM = $a->thesis->relationLoaded('milestones')
                        ? $a->thesis->milestones
                            ->filter(fn($m) => !in_array($m->status, ['completed', 'approved']))
                            ->sortBy(fn($m) => $m->template?->order ?? 999)
                            ->first()
                        : $a->thesis->currentMilestone;
                    $s->current_milestone_name = $currentM?->template?->name ?? 'Initiation Phase';
                    $s->current_milestone_order = $currentM?->template?->order ?? 1;
                    $s->has_pending_review = in_array($a->thesis_project_id, $pendingSupervisorMilestoneThesisIds);

                    // Compute last activity (latest submission or thesis update)
                    $latestSub = $a->thesis->milestones->flatMap->submissions->sortByDesc('created_at')->first();
                    $lastActivityDate = $latestSub?->created_at ?? $a->thesis->updated_at ?? $s->updated_at;
                    $s->last_activity_at = $lastActivityDate;
                    $s->days_inactive = $lastActivityDate ? (int) now()->diffInDays($lastActivityDate) : 0;
                    $s->is_stalled = ($s->days_inactive > 30 && $s->overall_progress < 100);
                }
                return $s;
            })->filter()->unique('id')->values();

            if ($supervisorStudents->isNotEmpty()) {
                $supervisionAnalytics = [
                    'total_students' => $supervisorStudents->count(),
                    'average_progress' => (int) round($supervisorStudents->avg('overall_progress')),
                    'action_needed_count' => $supervisorStudents->where('has_pending_review', true)->count(),
                    'stalled_count' => $supervisorStudents->where('is_stalled', true)->count(),
                    'stage_proposal' => $supervisorStudents->filter(fn($s) => ($s->current_milestone_order ?? 1) <= 2)->count(),
                    'stage_research' => $supervisorStudents->filter(fn($s) => ($s->current_milestone_order ?? 1) >= 3 && ($s->current_milestone_order ?? 1) <= 5)->count(),
                    'stage_defense' => $supervisorStudents->filter(fn($s) => ($s->current_milestone_order ?? 1) >= 6)->count(),
                ];
            }

            // Pending seminar and defence examinations where supervisor is a panel member
            $pendingSeminars = \App\Models\DefenceEvent::whereIn('type', ['seminar', 'proposal', 'progress_report_1', 'progress_report_2'])
                ->whereHas('panelMembers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->whereDoesntHave('evaluations', function($q) use ($user) {
                    $q->where('evaluator_id', $user->id);
                })
                ->whereHas('thesis.milestones', function($q) {
                    $q->whereNotNull('student_milestones.defence_date');
                })
                ->with(['thesis.student.user'])
                ->get();
        }

        $data['supervisor'] = $supervisor;
        $data['assignments'] = $supervisorAssignments;
        $data['students'] = $supervisorStudents;
        $data['pending_reviews'] = $pendingSupervisorReviews;
        $data['pending_seminars'] = $pendingSeminars;
        $data['supervisionAnalytics'] = $supervisionAnalytics;

        // 2. PROGRAM COORDINATION TELEMETRY
        $coordinatedPrograms = collect();
        $coordinatorStudents = collect();
        $coordinatorPendingReviews = collect();
        $coordinatorUpcomingEvents = collect();
        $coordinatorClearanceMetrics = ['m1' => 0, 'm2' => 0, 'm6' => 0];
        $coordinatorStats = ['students' => 0, 'theses' => 0, 'supervisors' => 0, 'pending_reviews' => 0];

        if ($isCoordinator) {
            $scopes = $user->coordinatorScopes();
            $allProgramIds = $scopes->pluck('program_id')->unique()->toArray();
            $allLevelIds = $scopes->pluck('level_id')->unique()->toArray();
            $coordinatedPrograms = \App\Models\Program::whereIn('id', $allProgramIds)->get();

            $coordinatorStudents = StudentProfile::forCoordinator($user)
                ->with(['user', 'program', 'level', 'thesis.milestones.template', 'thesis.assignments.supervisor.user'])
                ->where('enrollment_status', 'active')
                ->latest()
                ->take(25)
                ->get();

            $totalCoordStudents = StudentProfile::forCoordinator($user)->where('enrollment_status', 'active')->count();
            $totalCoordTheses = \App\Models\ThesisProject::whereHas('student', function($q) use ($user) {
                $q->forCoordinator($user);
            })->whereIn('status', ['active', 'proposed'])->count();
            $totalCoordSupervisors = SupervisorProfile::whereHas('programs', function($q) use ($allProgramIds) {
                $q->whereIn('programs.id', $allProgramIds);
            })->count();

            // Coordinator pending milestone clearances
            $coordinatorPendingReviews = \App\Models\StudentMilestone::whereHas('thesis.student', function($q) use ($user) {
                    $q->forCoordinator($user);
                })
                ->where('status', '!=', 'approved')
                ->whereHas('template', function($q) {
                    $q->whereJsonContains('required_approvers', 'Program Coordinator');
                })
                ->where(function($q) {
                    $q->whereNotNull('submitted_at')
                      ->orWhereHas('template', function($sq) {
                          $sq->where('requires_submission', false);
                      });
                })
                ->with(['thesis.student.user', 'template'])
                ->latest()
                ->get()
                ->filter(function($milestone) use ($user) {
                    return $this->isMilestonePendingReviewForUser($milestone, $user->id, 'Program Coordinator');
                })
                ->values();

            $coordinatorUpcomingEvents = \App\Models\DefenceEvent::whereHas('thesis.student', function($q) use ($user) {
                    $q->forCoordinator($user);
                })
                ->where('schedule_start', '>=', now())
                ->orderBy('schedule_start')
                ->take(5)
                ->get();

            $denom = $totalCoordStudents ?: 1;
            $approvedCounts = \App\Models\StudentMilestone::whereHas('thesis.student', function($q) use ($user) {
                    $q->forCoordinator($user);
                })
                ->where('student_milestones.status', 'approved')
                ->join('milestone_templates', 'student_milestones.milestone_template_id', '=', 'milestone_templates.id')
                ->whereIn('milestone_templates.order', [1, 2, 6])
                ->groupBy('milestone_templates.order')
                ->selectRaw('milestone_templates.order, count(*) as count')
                ->pluck('count', 'order');

            $coordinatorClearanceMetrics = [
                'm1' => round((($approvedCounts->get(1, 0)) / $denom) * 100),
                'm2' => round((($approvedCounts->get(2, 0)) / $denom) * 100),
                'm6' => round((($approvedCounts->get(6, 0)) / $denom) * 100),
            ];

            $coordinatorStats = [
                'students' => $totalCoordStudents,
                'theses' => $totalCoordTheses,
                'supervisors' => $totalCoordSupervisors,
                'pending_reviews' => $coordinatorPendingReviews->count(),
            ];
        }

        $data['coordinatedPrograms'] = $coordinatedPrograms;
        $data['coordinatorStudents'] = $coordinatorStudents;
        $data['coordinatorPendingReviews'] = $coordinatorPendingReviews;
        $data['coordinatorUpcomingEvents'] = $coordinatorUpcomingEvents;
        $data['coordinatorClearanceMetrics'] = $coordinatorClearanceMetrics;
        $data['coordinatorStats'] = $coordinatorStats;

        // 3. INTERNAL EXAMINATION TELEMETRY
        $internalTheses = collect();
        $internalPendingReviews = collect();

        if ($isInternalExaminer) {
            $internalProfileIds = $user->internalExaminerProfiles()->pluck('id')->toArray();
            $internalTheses = \App\Models\ThesisProject::whereIn('internal_examiner_profile_id', $internalProfileIds)
                ->with(['student.user', 'student.program', 'student.level', 'currentMilestone.template', 'milestones.template'])
                ->latest()
                ->get();

            $internalPendingReviews = \App\Models\StudentMilestone::whereHas('thesis', function($q) use ($internalProfileIds) {
                    $q->whereIn('internal_examiner_profile_id', $internalProfileIds);
                })
                ->where('status', '!=', 'approved')
                ->whereHas('template', function($q) {
                    $q->whereJsonContains('required_approvers', 'Internal Examiner');
                })
                ->whereNotNull('submitted_at')
                ->with(['thesis.student.user', 'template'])
                ->latest()
                ->get()
                ->filter(function($milestone) use ($user) {
                    return $this->isMilestonePendingReviewForUser($milestone, $user->id, 'Internal Examiner');
                })
                ->values();
        }

        $data['internalTheses'] = $internalTheses;
        $data['internalPendingReviews'] = $internalPendingReviews;

        // 4. EXTERNAL EXAMINATION TELEMETRY
        $externalTheses = collect();
        $externalPendingReviews = collect();

        if ($isExternalExaminer) {
            $externalProfileIds = $user->externalExaminerProfiles()->pluck('id')->toArray();
            $externalTheses = \App\Models\ThesisProject::whereIn('external_examiner_profile_id', $externalProfileIds)
                ->with(['student.user', 'student.program', 'student.level', 'currentMilestone.template', 'milestones.template'])
                ->latest()
                ->get();

            $externalPendingReviews = \App\Models\StudentMilestone::whereHas('thesis', function($q) use ($externalProfileIds) {
                    $q->whereIn('external_examiner_profile_id', $externalProfileIds);
                })
                ->where('status', '!=', 'approved')
                ->whereHas('template', function($q) {
                    $q->whereJsonContains('required_approvers', 'External Examiner');
                })
                ->whereNotNull('submitted_at')
                ->with(['thesis.student.user', 'template'])
                ->latest()
                ->get()
                ->filter(function($milestone) use ($user) {
                    return $this->isMilestonePendingReviewForUser($milestone, $user->id, 'External Examiner');
                })
                ->values();
        }

        $data['externalTheses'] = $externalTheses;
        $data['externalPendingReviews'] = $externalPendingReviews;

        // 5. VIVA / ORAL DEFENCE EVALUATIONS (Applicable to Examiners & Panelists)
        $data['pending_evaluations'] = \App\Models\DefenceEvent::whereHas('panelMembers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereDoesntHave('evaluations', function($q) use ($user) {
                $q->where('evaluator_id', $user->id);
            })->with(['thesis.student.user'])->get();

        // 6. UPCOMING ACADEMIC DEFENCES (Unified timeline across active portfolios)
        $upcomingDefences = \App\Models\DefenceEvent::where(function($q) use ($user, $supervisor, $isCoordinator, $isInternalExaminer, $isExternalExaminer) {
            $q->whereHas('panelMembers', fn($sq) => $sq->where('user_id', $user->id));
            if ($supervisor) {
                $q->orWhereHas('thesis.assignments', fn($sq) => $sq->where('supervisor_profile_id', $supervisor->id));
            }
            if ($isCoordinator) {
                $q->orWhereHas('thesis.student', fn($sq) => $sq->forCoordinator($user));
            }
            if ($isInternalExaminer) {
                $internalIds = $internalProfileIds ?? $user->internalExaminerProfiles()->pluck('id')->toArray();
                $q->orWhereHas('thesis', fn($sq) => $sq->whereIn('internal_examiner_profile_id', $internalIds));
            }
            if ($isExternalExaminer) {
                $externalIds = $externalProfileIds ?? $user->externalExaminerProfiles()->pluck('id')->toArray();
                $q->orWhereHas('thesis', fn($sq) => $sq->whereIn('external_examiner_profile_id', $externalIds));
            }
        })
        ->where('schedule_start', '>=', now()->subHours(6))
        ->with(['thesis.student.user', 'thesis.student.program', 'panelMembers.user'])
        ->orderBy('schedule_start', 'asc')
        ->take(8)
        ->get();

        $data['upcomingDefences'] = $upcomingDefences;

        // 7. GLOBAL MILESTONE TEMPLATES (for milestone jump)
        $data['milestone_templates'] = \Illuminate\Support\Facades\Cache::remember('all_milestone_templates', 3600, function() {
            return \App\Models\MilestoneTemplate::orderBy('order')->get();
        });

        // 7. AGGREGATED STATS MATRIX
        $unreadCounts = $this->getUnreadMessagesCount($user);
        $totalPendingReviews = $pendingSupervisorReviews->count() + $coordinatorPendingReviews->count() + $internalPendingReviews->count() + $externalPendingReviews->count() + $data['pending_evaluations']->count();

        $data['stats'] = [
            'assigned_students' => $supervisorStudents->count(),
            'pending_reviews' => $pendingSupervisorReviews->count(),
            'total_theses' => $supervisorAssignments->unique('thesis_project_id')->count(),
            'coordinator_students' => $coordinatorStats['students'],
            'coordinator_theses' => $coordinatorStats['theses'],
            'internal_theses' => $internalTheses->count(),
            'external_theses' => $externalTheses->count(),
            'total_pending_actions' => $totalPendingReviews,
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
            ->whereNotNull('submitted_at')
            ->with(['thesis.student.user', 'template'])
            ->latest()
            ->get()
            ->filter(function($milestone) use ($user, $roleName) {
                return $this->isMilestonePendingReviewForUser($milestone, $user->id, $roleName);
            })
            ->values();

        // Fetch pending defence evaluations
        $data['pending_evaluations'] = \App\Models\DefenceEvent::whereHas('panelMembers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereDoesntHave('evaluations', function($q) use ($user) {
                $q->where('evaluator_id', $user->id);
            })->whereHas('thesis.milestones', function($q) {
                $q->whereNotNull('student_milestones.defence_date');
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
                ->where(function($q) {
                    $q->whereNotNull('submitted_at')
                      ->orWhereHas('template', function($sq) {
                          $sq->where('requires_submission', false);
                      });
                })
                ->with(['thesis.student.user', 'template'])
                ->latest()
                ->get()
                ->filter(function($milestone) use ($user) {
                    return $this->isMilestonePendingReviewForUser($milestone, $user->id, 'Program Coordinator');
                })
                ->values();

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

    /**
     * Determine if a milestone is currently awaiting review/action from the specified user.
     * Enforces the multi-supervisor policy:
     * - Standing approvals: if a supervisor already approved, it stands across re-uploads.
     * - Pending re-review: if supervisor requested revision and candidate re-uploaded, it needs re-review.
     * - Inactive during candidate revision: if status is revision_required, candidate must act first.
     * - Upload acceptance completion: once a supervisor accepts upload or candidate is eligible to present
     *   on presentation-gated milestones, no further supervisor review action is required.
     */
    private function isMilestonePendingReviewForUser($milestone, $userId, ?string $role = null): bool
    {
        if ($milestone->status === 'approved') {
            return false;
        }

        if ($milestone->status === 'revision_required') {
            return false;
        }

        $approvals = $milestone->approvals ?? [];
        if (is_string($approvals)) {
            $approvals = json_decode($approvals, true) ?: [];
        }

        $userIdStr = (string) $userId;

        // 1. Multi-supervisor review tracking
        if (is_array($approvals) && isset($approvals['supervisor_reviews'][$userIdStr])) {
            $supReview = $approvals['supervisor_reviews'][$userIdStr];
            $decision = $supReview['decision'] ?? null;

            // Standing approval: supervisor already approved this milestone
            if ($decision === 'approved') {
                return false;
            }

            // Student re-uploaded after rejection -> awaiting re-review by this specific supervisor
            if ($decision === 'pending_re_review') {
                return true;
            }

            if ($decision === 'rejected') {
                return false;
            }
        }

        // 2. Supervisor-specific checks (via MilestoneWorkflowService & Feedback)
        if ($role === 'Supervisor' || empty($role)) {
            $workflow = app(\App\Services\MilestoneWorkflowService::class);
            $summary = $workflow->getSupervisorReviewSummary($milestone);

            $myReview = collect($summary['supervisors'] ?? [])->firstWhere('user_id', (int) $userId);
            if ($myReview) {
                if ($myReview['status'] === 'approved') {
                    return false;
                }
                if ($myReview['status'] === 'rejected') {
                    return false;
                }
                if ($myReview['status'] === 'pending_re_review') {
                    return true;
                }
                // If candidate is already eligible to present with 0 active rejections/re-reviews
                if ($summary['is_eligible'] && $summary['approved_count'] > 0 && $summary['rejected_count'] === 0 && $summary['pending_rereview_count'] === 0) {
                    return false;
                }
            }

            // For presentation-gated milestones (proposal defence, progress report 1 & 2):
            // The supervisor's role is upload acceptance. Once the milestone upload is accepted/eligible,
            // candidate is cleared to present and supervisor mentorship review is complete.
            if (in_array($milestone->template?->slug, \App\Services\MilestoneWorkflowService::PRESENTATION_GATED_SLUGS)) {
                if ($workflow->isSupervisorApproved($milestone)) {
                    return false;
                }
            }

            // Direct check on submissions feedback for this supervisor
            if ($milestone->relationLoaded('submissions')) {
                $hasFeedbackApproved = $milestone->submissions->contains(function($sub) use ($userId) {
                    if ($sub->relationLoaded('feedbacks')) {
                        return $sub->feedbacks->contains(fn($f) => $f->created_by == $userId && $f->decision === 'approved');
                    }
                    if ($sub->relationLoaded('feedback')) {
                        $fb = $sub->feedback;
                        if ($fb instanceof \Illuminate\Support\Collection) {
                            return $fb->contains(fn($f) => $f->created_by == $userId && $f->decision === 'approved');
                        }
                        return $fb && $fb->created_by == $userId && $fb->decision === 'approved';
                    }
                    return $sub->feedbacks()->where('created_by', $userId)->where('decision', 'approved')->exists();
                });
                if ($hasFeedbackApproved) {
                    return false;
                }
            } else {
                $hasFeedbackApproved = $milestone->submissions()
                    ->whereHas('feedbacks', fn($q) => $q->where('created_by', $userId)->where('decision', 'approved'))
                    ->exists();
                if ($hasFeedbackApproved) {
                    return false;
                }
            }

            // Direct check on is_supervisor_approved attribute/column
            if (!empty($milestone->is_supervisor_approved)) {
                if (!$myReview || $myReview['status'] !== 'pending_re_review') {
                    return false;
                }
            }
        }

        // 3. Role-keyed approval (e.g. 'Admin:1', 'Supervisor:2') or list of approval entries
        if (is_array($approvals)) {
            if ($role && isset($approvals["{$role}:{$userId}"])) {
                $item = $approvals["{$role}:{$userId}"];
                if (($item['decision'] ?? '') === 'approved' || !isset($item['decision'])) {
                    return false;
                }
            }

            foreach ($approvals as $key => $item) {
                if (is_array($item) && ($item['user_id'] ?? null) == $userId) {
                    if (($item['decision'] ?? '') === 'approved' || !isset($item['decision'])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
