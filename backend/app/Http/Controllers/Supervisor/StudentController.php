<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SupervisorProfile;

class StudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $supervisorProfileIds = SupervisorProfile::where('user_id', $user->id)->pluck('id');

        if ($supervisorProfileIds->isEmpty()) {
            abort(403, 'No active supervisor profile found.');
        }

        $query = \App\Models\SupervisionAssignment::whereIn('supervisor_profile_id', $supervisorProfileIds)
            ->where('status', 'active')
            ->with([
                'thesis.student.user', 
                'thesis.student.program',
                'thesis.currentMilestone.template',
                'thesis.assignments.supervisor.user',
                'thesis.milestones.template',
                'thesis.milestones.submissions.feedbacks',
            ]);

        if (request()->has('search') && request()->filled('search')) {
            $search = request()->input('search');
            $query->whereHas('thesis', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where('student_id_number', 'like', "%{$search}%")
                         ->orWhereHas('user', function($uq) use ($search) {
                             $uq->where('name', 'like', "%{$search}%");
                         })
                         ->orWhereHas('program', function($pq) use ($search) {
                             $pq->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $assignments = $query->paginate(15);

        return view('supervisor.students.index', compact('assignments'));
    }
}
