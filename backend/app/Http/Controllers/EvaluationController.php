<?php

namespace App\Http\Controllers;

use App\Models\DefenceEvent;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Show the form for creating a new evaluation for a defence event.
     */
    public function create(DefenceEvent $defenceEvent)
    {
        // Must be a panel member
        $isAuthorized = $defenceEvent->isAuthorizedEvaluator(Auth::id());
        if (!$isAuthorized) {
            abort(403, 'You are not a panel member for this defence event.');
        }

        // Check if already evaluated
        $existingEvaluation = Evaluation::where('defence_event_id', $defenceEvent->id)
            ->where('evaluator_id', Auth::id())
            ->first();

        if ($existingEvaluation && $existingEvaluation->submitted_at) {
            return redirect()->route('evaluations.show', $existingEvaluation)->with('info', 'You have already submitted an evaluation for this event.');
        }

        $defenceEvent->load(['thesis.student.user', 'thesis.student.program']);

        return view('evaluations.create', compact('defenceEvent', 'existingEvaluation'));
    }

    /**
     * Store a newly created evaluation in storage.
     */
    public function store(Request $request, DefenceEvent $defenceEvent)
    {
        $isAuthorized = $defenceEvent->isAuthorizedEvaluator(Auth::id());
        if (!$isAuthorized) {
            abort(403);
        }

        $isPassFail = in_array($defenceEvent->type, ['proposal', 'progress_report_1', 'progress_report_2']) || $request->has('verdict');

        if ($isPassFail) {
            $validated = $request->validate([
                'verdict' => 'required|in:pass,fail',
                'comments' => 'nullable|string|max:5000',
            ]);

            $verdict = strtolower($validated['verdict']);
            $score = [
                'verdict' => $verdict,
                'total' => ($verdict === 'pass' ? 100 : 0),
            ];
            $recommendation = $verdict;
            $comments = $validated['comments'] ?? null;
        } else {
            $validated = $request->validate([
                'score.originality' => 'required|integer|min:0|max:25',
                'score.methodology' => 'required|integer|min:0|max:25',
                'score.presentation' => 'required|integer|min:0|max:25',
                'score.qa' => 'required|integer|min:0|max:25',
                'recommendation' => 'required|in:pass,minor_revisions,major_revisions,fail',
                'comments' => 'nullable|string|max:2000',
            ]);

            $originality = (int) $validated['score']['originality'];
            $methodology = (int) $validated['score']['methodology'];
            $presentation = (int) $validated['score']['presentation'];
            $qa = (int) $validated['score']['qa'];
            $total = $originality + $methodology + $presentation + $qa;

            $score = [
                'originality' => $originality,
                'methodology' => $methodology,
                'presentation' => $presentation,
                'qa' => $qa,
                'total' => $total,
            ];
            $recommendation = $validated['recommendation'];
            $comments = $validated['comments'] ?? null;
        }

        $evaluation = Evaluation::updateOrCreate(
            [
                'defence_event_id' => $defenceEvent->id,
                'evaluator_id' => Auth::id(),
            ],
            [
                'score' => $score,
                'recommendation' => $recommendation,
                'comments' => $comments,
                'submitted_at' => now(),
            ]
        );

        // Mark evaluator as present (Requirement: only examiners that grade student are marked as present)
        \App\Models\PanelMember::where('defence_event_id', $defenceEvent->id)
            ->where('user_id', Auth::id())
            ->update(['is_present' => true]);

        // Deliver examiner comments & verdict directly to candidate's personal inbox (Requirement 3)
        $studentUser = $defenceEvent->thesis?->student?->user;
        if ($studentUser) {
            $examiner = Auth::user();
            $milestoneLabel = match($defenceEvent->type) {
                'proposal' => 'Proposal Defence',
                'progress_report_1' => 'Progress Report 1 Defence',
                'progress_report_2' => 'Progress Report 2 Defence',
                'seminar' => 'Seminar as a Course',
                default => ucfirst(str_replace('_', ' ', $defenceEvent->type)),
            };

            $verdictUpper = strtoupper($recommendation);
            $subject = "Evaluation Feedback: {$milestoneLabel} - {$verdictUpper}";

            $body = "Dear {$studentUser->name},\n\n"
                  . "Your {$milestoneLabel} defence has been evaluated by examiner {$examiner->name}.\n\n"
                  . "Result: {$verdictUpper}\n\n"
                  . "Examiner Comments & Feedback:\n"
                  . (!empty($comments) ? $comments : "No additional written comments provided.") . "\n\n"
                  . "Please review your progress on the student portal.";

            $inboxMsg = \App\Models\InboxMessage::create([
                'sender_id' => $examiner->id,
                'subject' => $subject,
                'body' => $body,
                'type' => 'general',
            ]);

            \App\Models\InboxMessageRecipient::create([
                'inbox_message_id' => $inboxMsg->id,
                'user_id' => $studentUser->id,
                'is_read' => false,
            ]);

            \App\Models\Notification::create([
                'user_id' => $studentUser->id,
                'title' => "Evaluation Feedback: {$milestoneLabel}",
                'message' => "Examiner {$examiner->name} submitted an evaluation for your {$milestoneLabel} ({$verdictUpper}). Check your inbox for comments.",
                'type' => 'evaluation_feedback',
                'link' => route('inbox.index'),
            ]);
        }

        // Dispatch Real-time events to Coordinators and Directors
        $coords = \App\Models\CoordinatorProfile::where('program_id', $defenceEvent->thesis->student->program_id)->where('active', true)->pluck('user_id');
        $directors = \App\Models\User::role('Director')->pluck('id');
        $recipients = $coords->merge($directors)->unique();

        foreach ($recipients as $userId) {
            \App\Events\EvaluationSubmitted::dispatch($evaluation, $userId);
        }

        $templateSlug = match($defenceEvent->type) {
            'proposal' => 'proposal_defence',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            'seminar' => 'seminar_as_a_course',
            default => null,
        };

        if ($templateSlug && \App\Models\MilestoneTemplate::where('slug', $templateSlug)->exists()) {
            return redirect()->route('presentations.show', $templateSlug)
                ->with('success', 'Evaluation submitted successfully. Written feedback and verdict have been delivered to candidate inbox.');
        }

        return redirect()->route('dashboard')->with('success', 'Evaluation submitted successfully. Written feedback and verdict have been delivered to candidate inbox.');
    }

    /**
     * Display the specified evaluation.
     */
    public function show(Evaluation $evaluation)
    {
        // Accessible by the evaluator or coordinator/director
        if (Auth::id() !== $evaluation->evaluator_id && !Auth::user()->hasRole(['Program Coordinator', 'Director', 'Admin']) && Auth::id() !== $evaluation->defenceEvent->thesis->student->user_id) {
            abort(403);
        }

        $evaluation->load(['defenceEvent.thesis.student.user', 'evaluator']);

        return view('evaluations.show', compact('evaluation'));
    }

    /**
     * Download the official evaluation report as a PDF.
     */
    public function downloadPdf(Evaluation $evaluation)
    {
        if (Auth::id() !== $evaluation->evaluator_id && !Auth::user()->hasRole(['Program Coordinator', 'Director', 'Admin']) && Auth::id() !== $evaluation->defenceEvent->thesis->student->user_id) {
            abort(403);
        }

        $evaluation->load(['defenceEvent.thesis.student.user', 'defenceEvent.thesis.student.program', 'evaluator']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.evaluation', compact('evaluation'));
        
        $filename = 'Evaluation_' . $evaluation->defenceEvent->thesis->student->user->name . '_' . $evaluation->id . '.pdf';
        
        return $pdf->download(str_replace(' ', '_', $filename));
    }
}
