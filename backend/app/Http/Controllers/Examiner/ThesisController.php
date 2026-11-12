<?php

namespace App\Http\Controllers\Examiner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ThesisProject;

class ThesisController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $internalProfileIds = $user->internalExaminerProfiles()->pluck('id')->toArray();
        $externalProfileIds = $user->externalExaminerProfiles()->pluck('id')->toArray();

        $theses = ThesisProject::with(['studentProfile.user', 'program'])
            ->where(function($query) use ($user, $internalProfileIds, $externalProfileIds) {
                if (!empty($internalProfileIds)) {
                    $query->orWhereIn('internal_examiner_profile_id', $internalProfileIds);
                }
                if (!empty($externalProfileIds)) {
                    $query->orWhereIn('external_examiner_profile_id', $externalProfileIds);
                }
                $query->orWhereHas('examinerAssignments', fn($eq) => $eq->where('examiner_id', $user->id))
                      ->orWhereHas('defenceEvents', fn($dq) => $dq->whereIn('type', ['internal', 'external'])->whereHas('panelMembers', fn($pq) => $pq->where('user_id', $user->id)));
            })
            ->latest()
            ->paginate(15);

        return view('examiner.theses.index', compact('theses', 'internalProfileIds', 'externalProfileIds'));
    }

    public function show($id)
    {
        $user = auth()->user();
        
        $internalProfileIds = $user->internalExaminerProfiles()->pluck('id')->toArray();
        $externalProfileIds = $user->externalExaminerProfiles()->pluck('id')->toArray();

        $thesis = ThesisProject::with([
                'studentProfile.user', 
                'program', 
                'milestones.submissions',
                'milestones.template'
            ])
            ->where('id', $id)
            ->where(function($query) use ($user, $internalProfileIds, $externalProfileIds) {
                if (!empty($internalProfileIds)) {
                    $query->orWhereIn('internal_examiner_profile_id', $internalProfileIds);
                }
                if (!empty($externalProfileIds)) {
                    $query->orWhereIn('external_examiner_profile_id', $externalProfileIds);
                }
                $query->orWhereHas('examinerAssignments', fn($eq) => $eq->where('examiner_id', $user->id))
                      ->orWhereHas('defenceEvents', fn($dq) => $dq->whereIn('type', ['internal', 'external'])->whereHas('panelMembers', fn($pq) => $pq->where('user_id', $user->id)));
            })
            ->firstOrFail();

        return view('examiner.theses.show', compact('thesis', 'internalProfileIds', 'externalProfileIds'));
    }
}
