<?php

namespace App\Services;

use App\Models\StudentProfile;
use App\Models\ThesisProject;
use App\Models\Program;
use App\Models\SupervisorProfile;
use App\Models\StudentMilestone;
use App\Models\MilestoneTemplate;
use App\Models\Cohort;
use App\Models\Message;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DirectorAnalyticsService
{
    /**
     * Get Section 1: Key Institutional Metrics
     */
    public function getInstitutionalMetrics($filters = [])
    {
        try {
            return [
                'total_students' => $this->applyFilters(StudentProfile::query(), $filters)->count(),
                'active_students' => $this->applyFilters(StudentProfile::where('enrollment_status', 'active'), $filters)->count(),
                'graduated_students' => $this->applyFilters(StudentProfile::where('enrollment_status', 'graduated'), $filters)->count(),
                'proposal_stage' => $this->applyFilters(StudentProfile::whereHas('thesis.currentMilestone.template', function($q) {
                    $q->where('name', 'ilike', '%proposal%');
                }), $filters)->count(),
                'internal_defence' => $this->applyFilters(StudentProfile::whereHas('thesis.currentMilestone.template', function($q) {
                    $q->where('name', 'ilike', '%internal%');
                }), $filters)->count(),
                'cleared_for_external' => $this->applyFilters(StudentProfile::whereHas('thesis', function($q) {
                    $q->whereNotNull('cleared_for_internal_at'); 
                }), $filters)->count(),
                
                'total_msc_students' => $this->applyFilters(StudentProfile::whereHas('level', function($q) {
                    $q->where('name', 'MSc');
                }), $filters)->count(),
                'total_phd_students' => $this->applyFilters(StudentProfile::whereHas('level', function($q) {
                    $q->where('name', 'PhD');
                }), $filters)->count(),
                'total_supervisors' => $this->applyFilters(SupervisorProfile::query(), $filters, 'supervisor')->count(),
                'active_cohorts' => $this->applyFilters(Cohort::where('status', 'active'), $filters, 'cohort')->count(),
            ];
        } catch (\Exception $e) {
            return [
                'total_students' => 0,
                'active_students' => 0,
                'graduated_students' => 0,
                'proposal_stage' => 0,
                'internal_defence' => 0,
                'cleared_for_external' => 0,
                'total_msc_students' => 0,
                'total_phd_students' => 0,
                'total_supervisors' => 0,
                'active_cohorts' => 0
            ];
        }
    }

    /**
     * Get Section 2: Program Performance Overview
     */
    public function getProgramPerformance($filters = [])
    {
        $programs = Program::all();
        $performance = [];

        foreach ($programs as $program) {
            $programFilters = array_merge($filters, ['program_id' => $program->id]);
            
            // Completion time logic: Average days between registration (Milestone 1 approval) 
            // and Final Submission/Approval (Milestone 10)
            $avgCompletionDays = StudentProfile::where('program_id', $program->id)
                ->where('enrollment_status', 'graduated')
                ->whereHas('thesis.milestones', fn($q) => $q->where('status', 'approved')->whereHas('template', fn($t) => $t->where('order', 1)))
                ->get()
                ->map(function($student) {
                    $m1 = $student->thesis->milestones->first(fn($m) => $m->template->order == 1);
                    $m10 = $student->thesis->milestones->first(fn($m) => $m->template->order == 10 || $m->template->is_final_archival);
                    if ($m1 && $m10 && $m1->approved_at && $m10->approved_at) {
                        return $m1->approved_at->diffInDays($m10->approved_at);
                    }
                    return null;
                })->filter()->avg() ?: 0;

            $performance[] = [
                'id' => $program->id,
                'name' => $program->name,
                'code' => $program->code,
                'total_students' => $this->applyFilters(StudentProfile::where('program_id', $program->id), $filters)->count(),
                'active_supervisors' => SupervisorProfile::whereHas('programs', function($q) use ($program) {
                    $q->where('programs.id', $program->id);
                })->count(),
                'proposal_stage' => $this->applyFilters(StudentProfile::where('program_id', $program->id)->whereHas('thesis.currentMilestone.template', function($q) {
                    $q->where('name', 'ilike', '%proposal%');
                }), $filters)->count(),
                'internal_stage' => $this->applyFilters(StudentProfile::where('program_id', $program->id)->whereHas('thesis.currentMilestone.template', function($q) {
                    $q->where('name', 'ilike', '%internal%');
                }), $filters)->count(),
                'cleared_external' => $this->applyFilters(StudentProfile::where('program_id', $program->id)->whereHas('thesis', function($q) {
                    $q->whereNotNull('cleared_for_internal_at');
                }), $filters)->count(),
                'avg_completion_time' => $avgCompletionDays > 0 ? round($avgCompletionDays / 30, 1) . ' months' : 'N/A',
                'delayed_students' => $this->getDelayedStudents($programFilters)->count(),
            ];
        }

        return $performance;
    }

    /**
     * Get Section 3: Milestone Progress Pipeline (Funnel)
     */
    public function getMilestonePipeline($filters = [])
    {
        $templates = MilestoneTemplate::whereNull('program_id')
            ->orWhere('program_id', $filters['program_id'] ?? null)
            ->orderBy('order')
            ->get();

        $pipeline = [];
        foreach ($templates as $template) {
            $count = StudentMilestone::where('milestone_template_id', $template->id)
                ->where('status', 'approved')
                ->whereHas('thesis.student', function($q) use ($filters) {
                    $this->applyFilters($q, $filters);
                })
                ->count();
            
            $pipeline[] = [
                'name' => $template->name,
                'count' => $count,
            ];
        }

        return $pipeline;
    }

    /**
     * Get Section 5: Delayed Students
     */
    public function getDelayedStudents($filters = [])
    {
        // Simple heuristic: status is not approved and due_date is passed
        $query = StudentMilestone::where('status', '!=', 'approved')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with(['thesis.student.user', 'thesis.student.program', 'thesis.assignments.supervisor.user', 'template']);

        return $this->applyFilters($query, $filters, 'student_milestone');
    }

    /**
     * Get Section 6: Supervisor Workload Analytics
     */
    public function getSupervisorWorkload($filters = [])
    {
        $assignmentFilter = function($q) use ($filters) {
            $q->where('status', 'active')
              ->whereHas('thesis.student', function($sq) use ($filters) {
                  $this->applyFilters($sq, $filters, 'student');
              });
        };

        $supervisors = SupervisorProfile::with([
            'user', 
            'programs', 
            'assignments' => $assignmentFilter,
            'assignments.thesis.student.level'
        ])
        ->withCount(['assignments' => $assignmentFilter]);

        $supervisors = $this->applyFilters($supervisors, $filters, 'supervisor');

        return $supervisors->get()->map(function($s) {
            $currentLoad = $s->assignments_count;
            $maxLoad = $s->max_load ?? 10;
            $loadPercentage = ($currentLoad / $maxLoad) * 100;

            return [
                'id' => $s->id,
                'name' => $s->user->name,
                'program' => $s->programs->pluck('code')->join(', ') ?: 'N/A',
                'msc_count' => $s->assignments->filter(fn($a) => ($a->thesis->student->level->name ?? '') === 'MSc')->count(),
                'phd_count' => $s->assignments->filter(fn($a) => ($a->thesis->student->level->name ?? '') === 'PhD')->count(),
                'total' => $currentLoad,
                'max_load' => $maxLoad,
                'load_percentage' => round($loadPercentage, 1),
                'status' => $loadPercentage >= 100 ? 'overloaded' : ($loadPercentage >= 80 ? 'near_capacity' : 'optimal')
            ];
        })->sortByDesc('load_percentage')->values();
    }

    /**
     * Get Section 4: Upcoming Defence Schedule
     */
    public function getUpcomingDefences($filters = [])
    {
        $query = \App\Models\DefenceEvent::with(['thesis.student.user', 'thesis.student.program', 'thesis.assignments.supervisor.user'])
            ->where('schedule_start', '>=', now())
            ->orderBy('schedule_start', 'asc');

        $query->whereHas('thesis.student', function($q) use ($filters) {
            $this->applyFilters($q, $filters, 'student');
        });

        return $query->take(10)->get();
    }

    /**
     * Get Section 7: Communication Health
     */
    public function getCommunicationHealth($filters = [])
    {
        $totalThreads = \App\Models\CommunicationChannel::count();
        $inactiveThreads = \App\Models\CommunicationChannel::where('updated_at', '<', now()->subDays(14))->count();
        
        // Calculate average supervisor response time
        // Heuristic: Time between a student message and the subsequent teacher/supervisor reply in the same channel
        $avgResponseHours = DB::table('messages as m1')
            ->join('messages as m2', function($join) {
                $join->on('m1.channel_id', '=', 'm2.channel_id')
                    ->whereColumn('m2.created_at', '>', 'm1.created_at');
            })
            ->join('model_has_roles as mhr1', function($join) {
                $join->on('m1.user_id', '=', 'mhr1.model_id')->where('mhr1.model_type', 'App\Models\User');
            })
            ->join('roles as r1', 'mhr1.role_id', '=', 'r1.id')
            ->join('model_has_roles as mhr2', function($join) {
                $join->on('m2.user_id', '=', 'mhr2.model_id')->where('mhr2.model_type', 'App\Models\User');
            })
            ->join('roles as r2', 'mhr2.role_id', '=', 'r2.id')
            ->where('r1.name', 'Student')
            ->whereIn('r2.name', ['Supervisor', 'Program Coordinator'])
            ->select(DB::raw('AVG(EXTRACT(EPOCH FROM (m2.created_at - m1.created_at))/3600) as avg_hours'))
            ->value('avg_hours') ?: 0;

        $waitingForFeedback = StudentMilestone::where('status', 'submitted')
            ->whereHas('thesis.student', function($q) use ($filters) {
                $this->applyFilters($q, $filters);
            })->count();

        return [
            'active_threads' => $totalThreads - $inactiveThreads,
            'inactive_threads' => $inactiveThreads,
            'avg_response_time' => $avgResponseHours > 0 
                ? ($avgResponseHours > 24 ? round($avgResponseHours/24, 1) . ' days' : round($avgResponseHours, 1) . ' hours')
                : 'N/A',
            'waiting_for_feedback' => $waitingForFeedback,
            'health_score' => $totalThreads > 0 ? round((($totalThreads - $inactiveThreads) / $totalThreads) * 100) : 100,
        ];
    }

    /**
     * Get Section 8: Cohort Monitoring
     */
    public function getCohortMonitoring($filters = [])
    {
        $query = Cohort::withCount(['students as total_students'])
             ->orderBy('intake_year', 'desc');

        $query = $this->applyFilters($query, $filters, 'cohort');

        return $query->take(10)->get()->map(function($cohort) {
            $completed_students = StudentProfile::where('cohort_id', $cohort->id)->where('enrollment_status', 'graduated')->count();
            $completion_rate = $cohort->total_students > 0 ? round(($completed_students / $cohort->total_students) * 100) . '%' : '0%';
            return [
                'name' => $cohort->name,
                'year' => $cohort->intake_year,
                'program' => 'All Programs',
                'students_count' => $cohort->total_students,
                'completion_rate' => $completion_rate,
            ];
        });
    }

    /**
     * Get Section 10: Granular Student Status List
     */
    public function getStudentStatusList($filters = [])
    {
        $query = StudentProfile::with(['user', 'program', 'level', 'cohort', 'thesis.currentMilestone.template']);
        
        $query = $this->applyFilters($query, $filters, 'student');
        
        return $query->latest()->paginate(15)->withQueryString();
    }

    /**
     * Get Section 9: System Activity Monitor
     */
    public function getSystemActivity($filters = [])
    {
        $query = AuditLog::with(['user'])
            ->latest();

        return $query->take(15)->get();
    }

    /**
     * Get Section 11: Stalled Students (Milestone Grace Periods)
     * Identifies students who have been stuck on a milestone for too long.
     */
    public function getStalledStudents($filters = [])
    {
        $gracePeriodDays = 14; 
        
        $query = StudentMilestone::with(['thesis.student.user', 'thesis.student.program', 'template', 'thesis.assignments.supervisor.user'])
            ->where('status', '!=', 'approved')
            ->where('updated_at', '<', now()->subDays($gracePeriodDays))
            ->whereHas('thesis.student', function($q) {
                $q->where('enrollment_status', 'active');
            });

        return $this->applyFilters($query, $filters, 'student_milestone')->orderBy('updated_at', 'asc');
    }

    /**
     * Get Section 12: Active Mentors (Leaderboard)
     * Ranks supervisors by graduation rates and response speed.
     */
    public function getFacultyLeaderboard($filters = [])
    {
        $supervisors = SupervisorProfile::with(['user', 'programs'])
            ->withCount(['assignments as total_graduated' => function($q) {
                $q->whereHas('thesis.student', fn($sq) => $sq->where('enrollment_status', 'graduated'));
            }])
            ->get();

        return $supervisors->map(function($s) {
            // Get avg response time for THIS supervisor
            $avgResponseHours = DB::table('messages as m1')
                ->join('messages as m2', function($join) {
                    $join->on('m1.channel_id', '=', 'm2.channel_id')
                        ->whereColumn('m2.created_at', '>', 'm1.created_at');
                })
                ->where('m2.user_id', $s->user_id)
                ->whereExists(function ($query) use ($s) {
                    $query->select(DB::raw(1))
                        ->from('model_has_roles')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->whereColumn('model_has_roles.model_id', 'messages.user_id')
                        ->where('roles.name', 'Student')
                        ->where('messages.channel_id', 'm1.channel_id');
                }, 'm1')
                ->select(DB::raw('AVG(EXTRACT(EPOCH FROM (m2.created_at - m1.created_at))/3600) as avg_hours'))
                ->value('avg_hours') ?: 0;

            return [
                'id' => $s->id,
                'name' => $s->user->name,
                'graduated_count' => $s->total_graduated,
                'avg_response_hours' => round($avgResponseHours, 1),
                'response_time_display' => $avgResponseHours > 0 
                    ? ($avgResponseHours > 24 ? round($avgResponseHours/24, 1) . 'd' : round($avgResponseHours, 1) . 'h')
                    : 'N/A',
                'score' => ($s->total_graduated * 10) - ($avgResponseHours / 24), // Simple ranking score
            ];
        })->sortByDesc('score')->values()->take(5);
    }

    /**
     * Helper to apply common filters
     */
    private function applyFilters($query, $filters, $entity = 'student')
    {
        if ($entity === 'student') {
            if (isset($filters['program_id'])) $query->where('program_id', $filters['program_id']);
            if (isset($filters['level_id']))   $query->where('level_id', $filters['level_id']);
            if (isset($filters['cohort_id']))  $query->where('cohort_id', $filters['cohort_id']);
            if (isset($filters['year']))       $query->whereYear('created_at', $filters['year']);
            
            if (isset($filters['search_student']) && !empty($filters['search_student'])) {
                $search = $filters['search_student'];
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('email', 'ilike', "%{$search}%");
                })->orWhere('student_id_number', 'ilike', "%{$search}%");
            }
        } elseif ($entity === 'supervisor') {
            if (isset($filters['program_id'])) {
                $query->whereHas('programs', fn($q) => $q->where('programs.id', $filters['program_id']));
            }
        } elseif ($entity === 'cohort') {
            if (isset($filters['program_id'])) {
                $query->whereHas('students', fn($q) => $q->where('program_id', $filters['program_id']));
            }
            if (isset($filters['year'])) {
                $query->where('intake_year', $filters['year']);
            }
        } elseif ($entity === 'student_milestone') {
            $query->whereHas('thesis.student', function($q) use ($filters) {
                if (isset($filters['program_id'])) $q->where('program_id', $filters['program_id']);
                if (isset($filters['level_id']))   $q->where('level_id', $filters['level_id']);
                if (isset($filters['cohort_id']))  $q->where('cohort_id', $filters['cohort_id']);
                if (isset($filters['year']))       $q->whereYear('created_at', $filters['year']);
            });
        }

        return $query;
    }

    /**
     * Get Examiner Workload Analytics
     */
    public function getExaminerWorkload($filters = [])
    {
        $examiners = \App\Models\InternalExaminerProfile::with('user')
            ->withCount(['assignments' => function($q) {
                $q->where('status', 'active');
            }])->get();

        return $examiners->map(function($e) {
            $currentLoad = $e->assignments_count;
            $maxLoad = $e->max_load ?? 8;
            $loadPercentage = $maxLoad > 0 ? ($currentLoad / $maxLoad) * 100 : 0;

            return [
                'id' => $e->id,
                'name' => $e->user->name ?? 'Unknown',
                'program' => 'Internal Examiner', 
                'total' => $currentLoad,
                'max_load' => $maxLoad,
                'load_percentage' => round($loadPercentage, 1),
                'status' => $loadPercentage >= 100 ? 'overloaded' : ($loadPercentage >= 80 ? 'near_capacity' : 'optimal')
            ];
        })->sortByDesc('load_percentage')->values();
    }

    /**
     * Get Plagiarism/Ethics Violations
     */
    public function getPlagiarismAlerts($filters = [])
    {
        $query = \App\Models\Submission::whereNotNull('plagiarism_data')
            ->whereHas('milestone.thesis.student', function($q) use ($filters) {
                $this->applyFilters($q, $filters, 'student');
            })
            ->with(['milestone.thesis.student.user', 'milestone.template'])
            ->latest()
            ->get();
            
        return $query->filter(function($sub) {
            // Check for high similarity or flagged status
            $score = $sub->plagiarism_data['similarity_index'] ?? ($sub->plagiarism_data['similarity_score'] ?? 0);
            return floatval($score) > 20; // 20% threshold
        })->take(5);
    }
}
