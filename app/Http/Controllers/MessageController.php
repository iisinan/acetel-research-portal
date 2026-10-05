<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use App\Http\Requests\SendMessageRequest;
use App\Models\ThesisProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function store(SendMessageRequest $request)
    {
        $thesis = ThesisProject::findOrFail($request->thesis_project_id);
        $user = Auth::user();

        // Auth Check
        if ($user->hasRole('Student') && $thesis->student_profile_id !== $user->studentProfile->id) {
            abort(403, 'Unauthorized');
        }
        if ($user->hasRole('Supervisor')) {
             $isAssigned = $thesis->assignments()->where('supervisor_profile_id', $user->supervisorProfile->id)->exists();
             if (!$isAssigned) {
                 abort(403, 'Unauthorized');
             }
        }
        
        if ($user->hasRole('Program Coordinator')) {
            $isCoordinatorForProgram = $user->coordinatorProfiles()
                ->where('active', true)
                ->where('program_id', $thesis->student?->program_id)
                ->exists();
                
            if (!$isCoordinatorForProgram) {
                abort(403, 'Unauthorized: This student is not in your program.');
            }
        }

        try {
            $message = $this->messageService->sendMessage(
                $thesis, 
                $user, 
                $request->input('content'), 
                $request->input('student_milestone_id'), 
                [], 
                $request->input('reply_to_id')
            );
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'html' => view('messages.single', compact('message'))->render()
                ], 201);
            }
            return redirect()->back()->with('success', 'Message sent.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
