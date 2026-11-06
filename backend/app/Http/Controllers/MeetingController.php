<?php

namespace App\Http\Controllers;

use App\Models\StudentMilestone;
use App\Models\DefenceEvent;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function join(Request $request, StudentMilestone $milestone)
    {
        $link = $milestone->meeting_link;

        if (!empty($link) && filter_var($link, FILTER_VALIDATE_URL)) {
            return redirect()->away($link);
        }

        // Check if defense event has location link
        $event = $milestone->thesis?->defenceEvents()->latest()->first();
        if ($event && !empty($event->location) && filter_var($event->location, FILTER_VALIDATE_URL)) {
            return redirect()->away($event->location);
        }

        return back()->with('info', 'Virtual presentation room: A Zoom meeting link has not yet been assigned by the administrator for this session.');
    }

    public function joinEvent(Request $request, DefenceEvent $event)
    {
        $link = $event->location;

        if (!empty($link) && filter_var($link, FILTER_VALIDATE_URL)) {
            return redirect()->away($link);
        }

        // Check milestone meeting_link
        $milestone = StudentMilestone::where('thesis_project_id', $event->thesis_project_id)
            ->whereNotNull('meeting_link')
            ->latest()
            ->first();

        if ($milestone && !empty($milestone->meeting_link) && filter_var($milestone->meeting_link, FILTER_VALIDATE_URL)) {
            return redirect()->away($milestone->meeting_link);
        }

        return back()->with('info', 'Virtual presentation room: A Zoom meeting link has not yet been assigned by the administrator for this defence event.');
    }
}
