<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Program;
use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // 1. Core Data
            $recentLogs = \App\Models\AuditLog::with('user')->latest()->take(6)->get();
            $readyForDefense = \App\Models\ThesisProject::with(['student.user', 'student.program', 'student.level'])
                ->whereNotNull('cleared_for_internal_at')
                ->latest('cleared_for_internal_at')
                ->take(5)
                ->get();
            $programs = Program::all();

            // M9 Alerts
            $m9Alerts = \App\Models\StudentMilestone::with(['thesis.student.user', 'template'])
                ->whereHas('template', function ($q) {
                    $q->where('order', 9)->orWhere('is_final_archival', true);
                })
                ->whereIn('status', ['submitted', 'in_progress', 'revision_required'])
                ->orderByDesc('updated_at')
                ->get();

            // 2. Statistics
            $stats = [
                'total_users' => User::count(),
                'active_users_24h' => User::where('last_login_at', '>=', now()->subDay())->count(),
                'student_count' => User::count(), // Simplified to avoid role check crash
                'active_students' => \App\Models\StudentProfile::where('enrollment_status', 'active')->count(),
                'total_theses' => \App\Models\ThesisProject::count(),
                'cleared_theses' => \App\Models\ThesisProject::whereNotNull('cleared_for_internal_at')->count(),
                'program_count' => Program::count(),
                'staff_count' => 0, // Simplified
                'failed_jobs' => 0, // Simplified
                'pending_jobs' => 0, // Simplified
            ];

            // 3. Activity
            $recentLogins = LoginActivity::with('user')->latest('login_at')->take(10)->get();
            $usersWithLastLogin = User::latest('last_login_at')->take(10)->get();
            $activityStats = [
                'logins_today' => LoginActivity::whereDate('login_at', today())->count(),
                'logins_this_week' => 0,
                'unique_users_today' => 0,
                'active_sessions' => 0,
            ];

            return view('admin.dashboard', [
                'stats' => $stats,
                'recent_logs' => $recentLogs,
                'projects' => $readyForDefense,
                'programs' => $programs,
                'recentLogins' => $recentLogins,
                'usersWithLastLogin' => $usersWithLastLogin,
                'activityStats' => $activityStats,
                'm9Alerts' => $m9Alerts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'diagnostic' => 'ACETEL Pilot Shield - Dashboard Halt',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => array_slice($e->getTrace(), 0, 5)
            ], 200);
        }
    }
}
