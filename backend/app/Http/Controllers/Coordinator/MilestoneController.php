<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentMilestone;

class MilestoneController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $scopes = $user->coordinatorScopes();
        
        \Illuminate\Support\Facades\Log::info('MilestoneController@index: User ID ' . $user->id . ' Roles: ' . json_encode($user->getRoleNames()) . ' Scopes count: ' . $scopes->count() . ' Programs: ' . \App\Models\Program::count());

        if ($scopes->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'No active coordinator program assigned. Please contact the administrator.');
        }

        $isAdminOrDirector = $user->hasAnyRole(['Admin', 'Director']);
        $programIds = $scopes->pluck('program_id')->unique()->toArray();

        $query = StudentMilestone::query()
            ->when(!$isAdminOrDirector, function ($q) use ($programIds) {
                $q->whereHas('thesis.student', function($sq) use ($programIds) {
                    $sq->whereIn('program_id', $programIds);
                });
            })
            ->with(['thesis.student.user', 'template', 'submissions']);

        if ($request->filled('thesis_id')) {
            $query->where('thesis_project_id', $request->thesis_id);
        }

        $milestones = $query->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('coordinator.milestones.index', compact('milestones'));
    }
}
