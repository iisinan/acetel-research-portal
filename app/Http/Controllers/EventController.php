<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use App\Http\Requests\ScheduleEventRequest;
use App\Http\Requests\EvaluationRequest;
use App\Models\DefenceEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function create()
    {
        $this->authorize('create', DefenceEvent::class);
        return view('events.create');
    }

    public function schedule(ScheduleEventRequest $request)
    {
        $this->authorize('create', DefenceEvent::class);

        try {
            $event = $this->eventService->scheduleEvent($request->validated());
            if ($request->wantsJson()) {
                return response()->json($event, 201);
            }
            return redirect()->back()->with('success', 'Event scheduled successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function showEvaluationForm(DefenceEvent $event)
    {
        $this->authorize('evaluate', $event);
        $event->load('thesis.student.user');
        return view('events.evaluate', compact('event'));
    }

    public function evaluate(EvaluationRequest $request, DefenceEvent $event)
    {
        $this->authorize('evaluate', $event);

        try {
            $evaluation = $this->eventService->submitEvaluation(
                $event,
                Auth::user(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json($evaluation, 201);
            }
            return redirect()->back()->with('success', 'Evaluation submitted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
