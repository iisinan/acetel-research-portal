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
use Illuminate\Support\Facades\DB;

class InboxController extends Controller
{
    /**
     * Display the inbox as a modern chat interface with previous message threads.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // 1. Gather all interactions (received and sent) to group conversations by partner
        $receivedInteractions = DB::table('inbox_messages')
            ->join('inbox_message_recipients', 'inbox_messages.id', '=', 'inbox_message_recipients.inbox_message_id')
            ->where('inbox_message_recipients.user_id', $userId)
            ->where('inbox_message_recipients.is_archived', false)
            ->select('inbox_messages.sender_id as partner_id', 'inbox_messages.created_at', 'inbox_message_recipients.read_at')
            ->get();

        $sentInteractions = DB::table('inbox_messages')
            ->join('inbox_message_recipients', 'inbox_messages.id', '=', 'inbox_message_recipients.inbox_message_id')
            ->where('inbox_messages.sender_id', $userId)
            ->where('inbox_messages.archived_by_sender', false)
            ->select('inbox_message_recipients.user_id as partner_id', 'inbox_messages.created_at')
            ->get()
            ->map(function ($item) {
                $item->read_at = now();
                return $item;
            });

        $interactions = $receivedInteractions->concat($sentInteractions);

        // Group by partner and calculate stats
        $partnerStats = $interactions
            ->filter(fn($item) => !empty($item->partner_id) && $item->partner_id !== $userId)
            ->groupBy('partner_id')
            ->map(function ($items, $partnerId) {
                return [
                    'partner_id' => $partnerId,
                    'last_message_at' => $items->max('created_at'),
                    'unread_count' => $items->whereNull('read_at')->count(),
                ];
            })
            ->sortByDesc('last_message_at');

        $partnerIds = $partnerStats->pluck('partner_id')->values();

        // Load partner users with roles and profiles
        $partnerUsers = User::whereIn('id', $partnerIds)
            ->with(['roles', 'studentProfile.program', 'supervisorProfile'])
            ->get()
            ->keyBy('id');

        // Fetch latest messages for each conversation partner in bulk
        $latestMessages = InboxMessage::where(function ($q) use ($userId, $partnerIds) {
            $q->whereIn('sender_id', $partnerIds)
              ->whereHas('recipients', fn($r) => $r->where('user_id', $userId)->where('is_archived', false));
        })->orWhere(function ($q) use ($userId, $partnerIds) {
            $q->where('sender_id', $userId)
              ->where('archived_by_sender', false)
              ->whereHas('recipients', fn($r) => $r->whereIn('user_id', $partnerIds));
        })
        ->latest()
        ->get();

        // Build sorted collection of conversations
        $conversations = collect();
        foreach ($partnerStats as $stats) {
            $pId = $stats['partner_id'];
            $user = $partnerUsers->get($pId);
            if (!$user) continue;

            $lastMsg = $latestMessages->first(function ($msg) use ($userId, $pId) {
                return ($msg->sender_id === $pId && $msg->recipients->pluck('id')->contains($userId)) ||
                       ($msg->sender_id === $userId && $msg->recipients->pluck('id')->contains($pId));
            });

            if (!$lastMsg) continue;

            $conversations->push((object)[
                'partner' => $user,
                'last_message' => $lastMsg,
                'last_message_at' => \Carbon\Carbon::parse($lastMsg->created_at),
                'unread_count' => $stats['unread_count'] ?? 0,
            ]);
        }

        // Determine currently active conversation partner
        $selectedUserId = $request->query('user_id');
        if (!$selectedUserId && $conversations->isNotEmpty()) {
            $selectedUserId = $conversations->first()->partner->id;
        }

        $selectedPartner = null;
        $chatMessages = collect();

        if ($selectedUserId) {
            $selectedPartner = User::where('id', $selectedUserId)
                ->with(['roles', 'studentProfile.program', 'supervisorProfile'])
                ->first();

            if ($selectedPartner) {
                // Fetch full chronological message thread (all previous messages)
                $chatMessages = InboxMessage::where(function ($q) use ($userId, $selectedUserId) {
                    $q->where('sender_id', $selectedUserId)
                      ->whereHas('recipients', fn($r) => $r->where('user_id', $userId)->where('is_archived', false));
                })->orWhere(function ($q) use ($userId, $selectedUserId) {
                    $q->where('sender_id', $userId)
                      ->where('archived_by_sender', false)
                      ->whereHas('recipients', fn($r) => $r->where('user_id', $selectedUserId));
                })
                ->with(['sender', 'attachments'])
                ->orderBy('created_at', 'asc')
                ->get();

                // Mark unread messages from this partner as read
                $unreadMessageIds = $chatMessages->where('sender_id', $selectedUserId)->pluck('id');
                if ($unreadMessageIds->isNotEmpty()) {
                    DB::table('inbox_message_recipients')
                        ->where('user_id', $userId)
                        ->whereNull('read_at')
                        ->whereIn('inbox_message_id', $unreadMessageIds)
                        ->update(['read_at' => now()]);
                }

                // Clear unread count for this partner in conversations collection
                $activeConv = $conversations->firstWhere('partner.id', $selectedUserId);
                if ($activeConv) {
                    $activeConv->unread_count = 0;
                }
            }
        }

        // Global unread count
        $unreadCount = DB::table('inbox_message_recipients')
            ->where('user_id', '=', $userId)
            ->whereNull('read_at')
            ->where('is_archived', '=', false)
            ->count();

        // Available contacts for starting new chats / modal
        $availableRecipients = $this->getAvailableRecipients();

        return view('inbox.index', compact(
            'conversations',
            'selectedPartner',
            'chatMessages',
            'unreadCount',
            'availableRecipients'
        ));
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
        $this->expandGroupRecipients($request);

        $validated = $request->validate([
            'to' => 'required|array|min:1',
            'to.*' => 'exists:users,id',
            'cc' => 'nullable|array',
            'cc.*' => 'exists:users,id',
            'bcc' => 'nullable|array',
            'bcc.*' => 'exists:users,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
            'delivery_method' => 'nullable|in:in_app,email,both',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        $allowedIds = $this->getAvailableRecipients(true)->pluck('id')->toArray();
        
        $subject = !empty($validated['subject']) 
            ? $validated['subject'] 
            : ($request->input('default_subject') ?: 'Direct Message');

        $deliveryMethod = $validated['delivery_method'] ?? 'both';

        $message = InboxMessage::create([
            'sender_id' => Auth::id(),
            'subject' => $subject,
            'body' => $validated['body'],
            'delivery_method' => $deliveryMethod,
        ]);

        // Attach recipients (To, CC, BCC)
        $this->attachRecipients($message, $validated, $allowedIds);

        // Handle Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('inbox_attachments', 'public');
                $message->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        $recipientId = $validated['to'][0] ?? null;

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        if ($recipientId) {
            return redirect()->route('inbox.index', ['user_id' => $recipientId])
                ->with('success', 'Message sent.');
        }

        return redirect()->route('inbox.index')->with('success', 'Message sent successfully.');
    }

    private function expandGroupRecipients(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Admin', 'Director', 'Program Coordinator'])) {
            return;
        }

        foreach (['to', 'cc', 'bcc'] as $field) {
            $input = $request->input($field, []);
            if (!is_array($input)) continue;

            $expanded = [];
            foreach ($input as $id) {
                if (str_starts_with($id, 'group:')) {
                    $expanded = array_merge($expanded, $this->resolveGroupUsers($id, $user));
                } else {
                    $expanded[] = $id;
                }
            }
            $request->merge([$field => array_unique($expanded)]);
        }
    }

    private function resolveGroupUsers($groupId, $user)
    {
        // Only Admin/Director can use global broadcasts
        if ($user->hasAnyRole(['Admin', 'Director'])) {
            if ($groupId === 'group:all_students') {
                return \App\Models\User::role('Student')->where('is_active', true)->pluck('id')->toArray();
            }
            if ($groupId === 'group:all_supervisors') {
                return \App\Models\User::role('Supervisor')->where('is_active', true)->pluck('id')->toArray();
            }
            if (str_starts_with($groupId, 'group:cohort_')) {
                $cohortId = str_replace('group:cohort_', '', $groupId);
                $studentIds = \App\Models\StudentProfile::where('cohort_id', $cohortId)->pluck('user_id');
                return \App\Models\User::whereIn('id', $studentIds)->where('is_active', true)->pluck('id')->toArray();
            }
        }
        return [];
    }

    private function attachRecipients($message, $validated, $allowedIds)
    {
        $allRecipients = [];

        foreach (['to', 'cc', 'bcc'] as $type) {
            if (!empty($validated[$type])) {
                $attachData = [];
                $userIdsToNotify = [];
                
                foreach ($validated[$type] as $userId) {
                    if (in_array($userId, $allowedIds)) {
                        $attachData[$userId] = [
                            'id' => (string) Str::uuid(),
                            'recipient_type' => $type
                        ];
                        $userIdsToNotify[] = $userId;
                        $allRecipients[] = $userId;
                    }
                }

                // Bulk attach recipients in chunks to avoid query limits
                $chunks = array_chunk($attachData, 500, true);
                foreach ($chunks as $chunk) {
                    $message->recipients()->attach($chunk);
                }
            }
        }

        // Dispatch job for notifications and broadcasts to prevent timeout
        $allRecipients = array_unique($allRecipients);
        if (!empty($allRecipients)) {
            \App\Jobs\ProcessMessageDelivery::dispatch($message, $allRecipients);
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

        // Determine partner to open chat with
        $partnerId = $isSender 
            ? $inboxMessage->recipients->first()?->id 
            : $inboxMessage->sender_id;

        if ($partnerId) {
            return redirect()->route('inbox.index', ['user_id' => $partnerId]);
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
    private function getAvailableRecipients($skipGroups = false)
    {
        $user = Auth::user();
        $recipientIds = collect();

        // Admin & Director: can message ALL users
        if ($user->hasRole(['Admin', 'Director'])) {
            $users = User::where('id', '!=', $user->id)
                ->where('is_active', '=', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            if ($skipGroups) {
                return $users;
            }

            $groups = collect([
                (object)[
                    'id' => 'group:all_students',
                    'name' => '📢 ALL STUDENTS (Broadcast)',
                    'email' => 'Message to every active student'
                ],
                (object)[
                    'id' => 'group:all_supervisors',
                    'name' => '📢 ALL SUPERVISORS (Broadcast)',
                    'email' => 'Message to every active supervisor'
                ],
            ]);

            $cohorts = \App\Models\Cohort::orderBy('intake_year', 'desc')->get();
            foreach ($cohorts as $cohort) {
                $groups->push((object)[
                    'id' => 'group:cohort_' . $cohort->id,
                    'name' => '📢 COHORT: ' . strtoupper($cohort->name) . ' (' . $cohort->intake_year . ')',
                    'email' => 'Message to all students in this cohort'
                ]);
            }

            return $groups->merge($users);
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
