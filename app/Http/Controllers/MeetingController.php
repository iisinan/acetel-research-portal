<?php

namespace App\Http\Controllers;

use App\Models\StudentMilestone;
use App\Models\DefenceEvent;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function join(Request $request, StudentMilestone $milestone)
    {
        $user = auth()->user();
        
        $milestone->load('thesis.student.user', 'template');
        
        $roomName = 'ACETEL-Presentation-' . $milestone->id . '-' . md5($milestone->thesis_project_id);
        
        $eventName = $milestone->template->name;
        $studentName = $milestone->thesis->student->user->name ?? 'Candidate';

        return view('meeting.join', compact('milestone', 'user', 'roomName', 'eventName', 'studentName'));
    }

    public function joinEvent(Request $request, DefenceEvent $event)
    {
        $user = auth()->user();
        
        $event->load('thesis.student.user');
        
        // Find corresponding milestone to keep room name consistent
        // If we can't find it, fallback to event ID
        $milestone = \App\Models\StudentMilestone::where('thesis_project_id', $event->thesis_project_id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required', 'approved', 'partially_approved'])
            ->latest()
            ->first();

        if ($milestone) {
            $roomName = 'ACETEL-Presentation-' . $milestone->id . '-' . md5($event->thesis_project_id);
        } else {
            $roomName = 'ACETEL-Event-' . $event->id . '-' . md5($event->thesis_project_id);
        }
        
        $eventName = ucfirst(str_replace('_', ' ', $event->type));
        $studentName = $event->thesis->student->user->name ?? 'Candidate';

        return view('meeting.join', compact('user', 'roomName', 'eventName', 'studentName'));
    }
}
