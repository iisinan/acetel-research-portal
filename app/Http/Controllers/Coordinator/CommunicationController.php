<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\ThesisProject;
use App\Models\CommunicationChannel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('Program Coordinator')) {
            $user->ensureCoordinatorProfiles();
        }
        $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
        $isAdminOrDirector = $user->hasAnyRole(['Admin', 'Director']);

        $channels = CommunicationChannel::when(!$isAdminOrDirector && $coordinatorProfile, function($query) use ($coordinatorProfile) {
            $query->whereHas('thesisProject.student', function($q) use ($coordinatorProfile) {
                $q->where('program_id', $coordinatorProfile->program_id);
            });
        })->with(['thesisProject.student.user', 'messages' => function($q) {
            $q->latest()->limit(1);
        }])->get()->map(function($channel) {
            $lastMessage = $channel->messages->first();
            $channel->last_message_at = $lastMessage ? $lastMessage->created_at : null;
            return $channel;
        });

        return view('coordinator.communications.index', compact('channels'));
    }

    public function show(CommunicationChannel $channel)
    {
        $channel->load(['thesisProject.student.user', 'thesisProject.assignments.supervisorProfile.user', 'messages.user']);
        
        return view('coordinator.communications.show', compact('channel'));
    }

    public function nudge(CommunicationChannel $channel)
    {
        $user = Auth::user();
        $thesis = $channel->thesisProject;
        
        // Notify student
        $thesis->student->user->notify(new \App\Notifications\NudgeParticipant($channel, $user));
        
        // Notify supervisors
        foreach ($thesis->assignments()->where('status', 'active')->get() as $assignment) {
            $assignment->supervisorProfile->user->notify(new \App\Notifications\NudgeParticipant($channel, $user));
        }

        return redirect()->back()->with('success', 'Nudge sent to all participants.');
    }
}
