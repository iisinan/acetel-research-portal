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
        $isPanelMember = $defenceEvent->panelMembers()->where('user_id', Auth::id())->exists();
        if (!$isPanelMember) {
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
        $isPanelMember = $defenceEvent->panelMembers()->where('user_id', Auth::id())->exists();
        if (!$isPanelMember) {
            abort(403);
        }

        $validated = $request->validate([
            'score.originality' => 'required|integer|min:0|max:10',
            'score.methodology' => 'required|integer|min:0|max:10',
            'score.presentation' => 'required|integer|min:0|max:10',
            'score.qa' => 'required|integer|min:0|max:10',
            'recommendation' => 'required|in:pass,minor_revisions,major_revisions,fail',
            'comments' => 'nullable|string|max:2000',
        ]);

        $evaluation = Evaluation::updateOrCreate(
            [
                'defence_event_id' => $defenceEvent->id,
                'evaluator_id' => Auth::id(),
            ],
            [
                'score' => [
                    'originality' => (int) $validated['score']['originality'],
                    'methodology' => (int) $validated['score']['methodology'],
                    'presentation' => (int) $validated['score']['presentation'],
                    'qa' => (int) $validated['score']['qa'],
                ],
                'recommendation' => $validated['recommendation'],
                'comments' => $validated['comments'],
                'submitted_at' => now(),
            ]
        );

        // Dispatch Real-time events to Coordinators and Directors
        $coords = \App\Models\CoordinatorProfile::where('program_id', $defenceEvent->thesis->student->program_id)->where('active', true)->pluck('user_id');
        $directors = \App\Models\User::role('Director')->pluck('id');
        $recipients = $coords->merge($directors)->unique();

        foreach ($recipients as $userId) {
            \App\Events\EvaluationSubmitted::dispatch($evaluation, $userId);
        }

        return redirect()->route('dashboard')->with('success', 'Evaluation submitted successfully.');
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
