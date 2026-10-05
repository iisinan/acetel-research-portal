<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display comprehensive activity logs with filtering.
     */
    public function index(Request $request)
    {
        // Login Activity Query
        $loginQuery = LoginActivity::with('user.roles')->latest('login_at');

        // Filters
        if ($request->filled('user_id')) {
            $loginQuery->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $loginQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $loginQuery->whereHas('user', function ($q) use ($request) {
                $q->role($request->role);
            });
        }

        if ($request->filled('date_from')) {
            $loginQuery->whereDate('login_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $loginQuery->whereDate('login_at', '<=', $request->date_to);
        }

        if ($request->filled('browser')) {
            $loginQuery->where('browser', $request->browser);
        }

        if ($request->filled('device_type')) {
            $loginQuery->where('device_type', $request->device_type);
        }

        $loginActivities = $loginQuery->paginate(25)->withQueryString();

        // Get filter option data
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->pluck('name');
        $browsers = LoginActivity::select('browser')->distinct()->whereNotNull('browser')->pluck('browser');

        // Summary stats
        $summaryStats = [
            'total_logins' => LoginActivity::count(),
            'unique_users' => LoginActivity::distinct('user_id')->count('user_id'),
            'logins_today' => LoginActivity::whereDate('login_at', today())->count(),
            'logins_this_week' => LoginActivity::where('login_at', '>=', now()->startOfWeek())->count(),
            'logins_this_month' => LoginActivity::where('login_at', '>=', now()->startOfMonth())->count(),
            'most_active_user' => LoginActivity::select('user_id')
                ->selectRaw('COUNT(*) as login_count')
                ->groupBy('user_id')
                ->orderByDesc('login_count')
                ->with('user')
                ->first(),
        ];

        return view('admin.activity-logs.index', compact(
            'loginActivities', 'users', 'roles', 'browsers', 'summaryStats'
        ));
    }

    /**
     * Show detailed user activity profile.
     */
    public function userActivity(User $user)
    {
        $loginHistory = LoginActivity::where('user_id', $user->id)
            ->latest('login_at')
            ->paginate(20);

        $userActions = AuditLog::where('user_id', $user->id)
            ->latest()
            ->take(50)
            ->get();

        $userStats = [
            'total_logins' => LoginActivity::where('user_id', $user->id)->count(),
            'first_login' => LoginActivity::where('user_id', $user->id)->oldest('login_at')->value('login_at'),
            'last_login' => LoginActivity::where('user_id', $user->id)->latest('login_at')->value('login_at'),
            'unique_ips' => LoginActivity::where('user_id', $user->id)->distinct('ip_address')->count('ip_address'),
            'browsers_used' => LoginActivity::where('user_id', $user->id)->distinct('browser')->pluck('browser'),
            'devices_used' => LoginActivity::where('user_id', $user->id)->distinct('device_type')->pluck('device_type'),
            'logins_this_week' => LoginActivity::where('user_id', $user->id)->where('login_at', '>=', now()->startOfWeek())->count(),
            'logins_this_month' => LoginActivity::where('user_id', $user->id)->where('login_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('admin.activity-logs.user', compact('user', 'loginHistory', 'userActions', 'userStats'));
    }
}
