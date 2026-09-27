<?php

namespace App\Http\Controllers;

use App\Models\InboxMessage;
use App\Models\InboxAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InboxController extends Controller
{
    /**
     * Display the inbox (received messages).
     */
    public function index()
    {
        $messages = InboxMessage::whereHas('recipients', function($q) {
            $q->where('user_id', '=', Auth::id())
              ->where('is_archived', '=', false);
        })
        ->with(['sender', 'recipients' => function($q) {
            $q->where('user_id', '=', Auth::id());
        }])
        ->latest()
        ->paginate(20);

        // Calculate unread count from pivot table
        $unreadCount = \Illuminate\Support\Facades\DB::table('inbox_message_recipients')
            ->where('user_id', '=', Auth::id())
            ->whereNull('read_at')
            ->where('is_archived', '=', false)
            ->count();

        return view('inbox.index', compact('messages', 'unreadCount'));
    }

    /**
     * Display sent messages.
     */
    public function sent()
    {
        $messages = InboxMessage::sentBy(Auth::id())
            ->with('recipients')
            ->latest()
            ->paginate(20);

        return view('inbox.sent', compact('messages'));
    }

    /**
     * Show compose form with role-filtered recipients.
     */
    public function compose()
    {
        Log::info('InboxController@compose hit for user: ' . Auth::id());
        $recipients = $this->getAvailableRecipients();
        Log::info('Recipients found: ' . count($recipients));
        return view('inbox.compose', compact('recipients'));
    }

    /**
     * Send a message.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|array|min:1',
            'to.*' => 'exists:users,id',
            'cc' => 'nullable|array',
            'cc.*' => 'exists:users,id',
            'bcc' => 'nullable|array',
            'bcc.*' => 'exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        $allowedIds = $this->getAvailableRecipients()->pluck('id')->toArray();
        
        $message = InboxMessage::create([
            'sender_id' => Auth::id(),
            'subject' => $validated['subject'],
            'body' => $validated['body'],
        ]);

        // Attach recipients (To, CC, BCC)
        $this->attachRecipients($message, $validated, $allowedIds);

        // Handle Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('inbox_attachments');
                $message->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('inbox.sent')->with('success', 'Message sent successfully.');
    }

    private function attachRecipients($message, $validated, $allowedIds)
    {
        foreach (['to', 'cc', 'bcc'] as $type) {
            if (!empty($validated[$type])) {
                foreach ($validated[$type] as $userId) {
                    if (in_array($userId, $allowedIds)) {
                        $message->recipients()->attach($userId, [
                            'id' => (string) Str::uuid(),
                            'recipient_type' => $type
                        ]);

                        // Broadcast to each recipient
                        \App\Events\MessageReceived::dispatch($message, $userId);
                    }
                }
            }
        }
    }

    /**
     * View a single message.
     */
    public function show(InboxMessage $inboxMessage)
    {
        $userId = Auth::id();
        $isSender = $inboxMessage->sender_id === $userId;
        $recipientRecord = $inboxMessage->recipients()->where('user_id', '=', $userId)->first();

        if (!$isSender && !$recipientRecord) {
            abort(403);
        }

        // Mark as read if a recipient is viewing
        if ($recipientRecord && !$recipientRecord->pivot->read_at) {
            $inboxMessage->recipients()->updateExistingPivot($userId, [
                'read_at' => now()
            ]);
        }

        $message = $inboxMessage->load(['sender', 'recipients', 'attachments']);
        return view('inbox.show', compact('message', 'isSender', 'recipientRecord'));
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(InboxAttachment $attachment)
    {
        $user = Auth::user();
        $message = $attachment->message;
        
        // Authorization: must be sender or recipient
        $isSender = $message->sender_id === $user->id;
        $isRecipient = $message->recipients()->where('user_id', '=', $user->id)->exists();
        
        if (!$isSender && !$isRecipient) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found on disk.');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Toggle star on a message.
     */
    public function star(InboxMessage $inboxMessage)
    {
        $userId = Auth::id();
        $recipientRecord = $inboxMessage->recipients()->where('user_id', '=', $userId)->first();
        
        if (!$recipientRecord) {
            abort(403);
        }

        $inboxMessage->recipients()->updateExistingPivot($userId, [
            'is_starred' => !$recipientRecord->pivot->is_starred
        ]);

        return back();
    }

    /**
     * Get available recipients based on user role.
     */
    private function getAvailableRecipients()
    {
        $user = Auth::user();
        $recipientIds = collect();

        // Admin & Director: can message ALL users
        if ($user->hasRole(['Admin', 'Director'])) {
            return User::where('id', '!=', $user->id)
                ->where('is_active', '=', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        // Program Coordinator: students in their programs, supervisors they added, admin, director
        if ($user->hasRole('Program Coordinator')) {
            // Get program IDs this coordinator manages
            $programIds = $user->coordinatorProfiles()
                ->where('active', '=', true)
                ->pluck('program_id');

            // Students in those programs
            $studentUserIds = \App\Models\StudentProfile::whereIn('program_id', $programIds)
                ->pluck('user_id');
            $recipientIds = $recipientIds->merge($studentUserIds);

            // Supervisors assigned to theses of those students
            $thesisIds = \App\Models\ThesisProject::whereIn('student_profile_id',
                \App\Models\StudentProfile::whereIn('program_id', $programIds)->pluck('id')
            )->pluck('id');

            $supervisorUserIds = \App\Models\SupervisionAssignment::whereIn('thesis_project_id', $thesisIds)
                ->where('status', 'active')
                ->with('supervisor')
                ->get()
                ->pluck('supervisor.user_id')
                ->filter();
            $recipientIds = $recipientIds->merge($supervisorUserIds);

            // Admin and Director users
            $adminDirectorIds = User::role(['Admin', 'Director'])->pluck('id');
            $recipientIds = $recipientIds->merge($adminDirectorIds);
        }

        // Student: supervisors, program coordinator, admin
        if ($user->hasRole('Student')) {
            $student = $user->studentProfile;
            if ($student && $student->thesis) {
                // Supervisors assigned to their thesis
                $supUserIds = $student->thesis->assignments()
                    ->where('status', '=', 'active')
                    ->with('supervisor')
                    ->get()
                    ->pluck('supervisor.user_id')
                    ->filter();
                $recipientIds = $recipientIds->merge($supUserIds);
            }

            // Program coordinator for their program
            if ($student) {
                $coordUserIds = \App\Models\CoordinatorProfile::where('program_id', $student->program_id)
                    ->where('active', '=', true)
                    ->pluck('user_id');
                $recipientIds = $recipientIds->merge($coordUserIds);
            }

            // Admin users
            $adminIds = User::role(['Admin'])->pluck('id');
            $recipientIds = $recipientIds->merge($adminIds);
        }

        // Supervisor: their students, coordinators, admin
        if ($user->hasRole('Supervisor')) {
            $supProfile = $user->supervisorProfile;
            if ($supProfile) {
                // Students they supervise
                $studentUserIds = $supProfile->assignments()
                    ->where('status', '=', 'active')
                    ->with('thesis.student')
                    ->get()
                    ->pluck('thesis.student.user_id')
                    ->filter();
                $recipientIds = $recipientIds->merge($studentUserIds);

                // Coordinators for those students' programs
                $programIds = \App\Models\StudentProfile::whereIn('user_id', $studentUserIds)->pluck('program_id')->unique();
                $coordUserIds = \App\Models\CoordinatorProfile::whereIn('program_id', $programIds)
                    ->where('active', '=', true)
                    ->pluck('user_id');
                $recipientIds = $recipientIds->merge($coordUserIds);
            }

            // Admin users
            $adminIds = User::role(['Admin'])->pluck('id');
            $recipientIds = $recipientIds->merge($adminIds);
        }

        $recipientIds = $recipientIds->unique()->reject(fn($id) => $id === $user->id);

        return User::whereIn('id', $recipientIds)
            ->where('is_active', '=', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
