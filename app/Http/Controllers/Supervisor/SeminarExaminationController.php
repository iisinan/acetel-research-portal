<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DefenceEvent;
use App\Models\Evaluation;
use App\Models\PanelMember;
use App\Models\InboxMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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
            ->with([
                'thesis.student.user',
                'thesis.student.program',
                'thesis.milestones' => function($q) {
                    $q->whereHas('template', function($q2) {
                        $q2->where('slug', 'seminar_as_a_course');
                    })->with('submissions', 'template');
                },
                'evaluations' => function($q) use ($user) {
                    $q->where('evaluator_id', $user->id);
                }
            ])
            ->orderBy('schedule_start')
            ->get();
            
        return view('supervisor.seminars.index', compact('events'));
    }

    public function storeScore(Request $request, $eventId)
    {
        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'recommendation' => 'nullable|string|in:pass,minor_revisions,major_revisions,fail',
            'comments' => 'nullable|string|max:5000'
        ]);

        $event = DefenceEvent::findOrFail($eventId);

        // Verification: ensure user is an assigned panel member for this defence
        $isPanelMember = $event->panelMembers()->where('user_id', Auth::id())->exists();
        if (!$isPanelMember && !Auth::user()->hasRole('Admin')) {
            abort(403, 'Institutional Protocol: You are not an assigned examiner for this candidate.');
        }

        // Allow grading on presentation day, after presentation, or up to 12 hours prior to start.
        if ($event->schedule_start && now()->lt($event->schedule_start->copy()->startOfDay()->subHours(12))) {
            return back()->with('error', 'Grading opens on the day of the candidate\'s presentation (' . $event->schedule_start->format('M d, Y') . ').');
        }
        
        $numericScore = (float)$request->score;
        $recommendation = $request->recommendation ?? ($numericScore >= 50 ? 'pass' : 'fail');

        $evaluation = Evaluation::updateOrCreate(
            [
                'defence_event_id' => $event->id,
                'evaluator_id' => Auth::id()
            ],
            [
                'score' => ['total' => $numericScore],
                'recommendation' => $recommendation,
                'comments' => $request->comments,
                'submitted_at' => now(),
            ]
        );

        // Mark panel member as present
        try {
            if (Schema::hasColumn('panel_members', 'is_present')) {
                PanelMember::where('defence_event_id', $event->id)
                    ->where('user_id', Auth::id())
                    ->update(['is_present' => true]);
            }
        } catch (\Throwable $e) {
            Log::warning('Updating panel member presence failed: ' . $e->getMessage());
        }

        // Dispatch feedback to candidate personal inbox
        $studentUser = $event->thesis?->student?->user;
        if ($studentUser) {
            $examiner = Auth::user();
            $recLabel = strtoupper(str_replace('_', ' ', $recommendation));
            $subject = "Seminar Evaluation Result: {$recLabel} ({$numericScore}/100)";
            $body = "Dear {$studentUser->name},\n\n"
                  . "Your Seminar Course presentation has been officially evaluated by examiner {$examiner->name}.\n\n"
                  . "Awarded Score: {$numericScore} / 100\n"
                  . "Recommendation: {$recLabel}\n\n"
                  . "Examiner Feedback & Remarks:\n"
                  . (!empty($request->comments) ? $request->comments : "No additional written remarks provided.") . "\n\n"
                  . "You can review your complete evaluation records on your student milestone portal.";

            try {
                $inboxMsg = InboxMessage::create([
                    'sender_id' => $examiner->id,
                    'subject' => $subject,
                    'body' => $body,
                    'delivery_method' => 'in_app',
                ]);
                $inboxMsg->recipients()->attach($studentUser->id, [
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Dispatching seminar evaluation inbox message failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', "Marks saved successfully ({$numericScore}/100 — " . strtoupper(str_replace('_', ' ', $recommendation)) . ").");
    }
}
