<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DefenceEvent;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Auth;

class SeminarExaminationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $events = DefenceEvent::where('type', 'seminar')
            ->whereHas('panelMembers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('thesis.milestones', function($q) {
                $q->whereNotNull('student_milestones.defence_date');
            })
            ->with(['thesis.student.user', 'thesis.milestones' => function($q) {
                $q->whereHas('template', function($q2) {
                    $q2->where('slug', 'seminar_as_a_course');
                })->with('submissions');
            }, 'evaluations' => function($q) use ($user) {
                $q->where('evaluator_id', $user->id);
            }])
            ->orderBy('schedule_start')
            ->get();
            
        return view('supervisor.seminars.index', compact('events'));
    }

    public function storeScore(Request $request, $eventId)
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string'
        ]);

        $event = DefenceEvent::findOrFail($eventId);

        if (now()->format('Y-m-d') !== $event->schedule_start->format('Y-m-d')) {
            return back()->with('error', 'You can only grade the student on the exact day of their presentation.');
        }
        
        Evaluation::updateOrCreate(
            [
                'defence_event_id' => $event->id,
                'evaluator_id' => Auth::id()
            ],
            [
                'score' => ['total' => $request->score],
                'comments' => $request->comments,
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Marks saved successfully.');
    }
}
