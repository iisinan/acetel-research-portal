<?php

namespace App\Http\Controllers;

use App\Models\StudentMilestone;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function join(Request $request, StudentMilestone $milestone)
    {
        $user = auth()->user();
        
        $milestone->load('thesis.student.user', 'template');
        
        // Define a unique deterministic room name based on milestone ID and thesis ID
        $roomName = 'ACETEL-Presentation-' . $milestone->id . '-' . md5($milestone->thesis_project_id);
        
        return view('meeting.join', compact('milestone', 'user', 'roomName'));
    }
}
