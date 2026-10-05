<?php

namespace App\Services;

use App\Models\Message;
use App\Models\ThesisProject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MessageService
{
    /**
     * Send a message in a thesis project.
     */
    public function sendMessage(ThesisProject $thesis, User $sender, ?string $content = null, ?string $milestoneId = null, array $meta = [], ?string $replyToId = null)
    {
        return DB::transaction(function () use ($thesis, $sender, $content, $milestoneId, $meta, $replyToId) {
            $type = 'text';
            $messageContent = $content;
            $filePath = null;

            // Handle File Attachment
            if (request()->hasFile('file')) {
                $file = request()->file('file');
                $filePath = $file->store('messages/' . $thesis->id, 'public');
                $type = 'file';
                $meta = array_merge($meta, [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
                
                if (!$messageContent) {
                    $messageContent = "Sent an attachment: " . $file->getClientOriginalName();
                }
            }

            $message = Message::create([
                'thesis_project_id' => $thesis->id,
                'student_milestone_id' => $milestoneId,
                'user_id' => $sender->id,
                'reply_to_id' => $replyToId,
                'content' => $messageContent ?? '',
                'type' => $type,
                'file_path' => $filePath,
                'meta' => $meta,
            ]);

            // Notify recipients based on milestone context + @ Mentions
            $recipients = collect();
            
            // 1. Identify @ Mentions
            $mentions = [];
            if ($message->content) {
                preg_match_all('/@([a-zA-Z0-9_\-\.]+)/', $message->content, $matches);
                if (!empty($matches[1])) {
                    $mentionedEmails = $matches[1];
                    $mentionedUsers = User::whereIn('email', $mentionedEmails)->get();
                    foreach ($mentionedUsers as $mu) {
                        $recipients->push($mu);
                        $mentions[] = $mu->id;
                    }
                }
            }
            if (!empty($mentions)) {
                $message->update(['meta' => array_merge($message->meta ?? [], ['mentions' => $mentions])]);
            }

            // 2. Standard Institutional Recipients
            $thesis->load(['student.user', 'assignments.supervisor.user', 'internalExaminer.user']);
            if ($thesis->student && $thesis->student->user_id !== $sender->id) {
                $recipients->push($thesis->student->user);
            }

            if ($milestoneId) {
                $milestone = \App\Models\StudentMilestone::with('template')->findOrFail($milestoneId);
                $order = $milestone->template->order;

                $coordinators = \App\Models\CoordinatorProfile::where('program_id', $thesis->student->program_id)
                    ->where('active', true)->with('user')->get();
                
                foreach ($coordinators as $cp) { $recipients->push($cp->user); }

                if ($order >= 2 && $order <= 6) {
                    foreach ($thesis->assignments as $assignment) {
                        if ($assignment->status === 'active') { $recipients->push($assignment->supervisor->user); }
                    }
                }

                if ($order >= 6 && $thesis->internalExaminer) {
                    $recipients->push($thesis->internalExaminer->user);
                }
            } else {
                foreach ($thesis->assignments as $assignment) {
                    if ($assignment->status === 'active') { $recipients->push($assignment->supervisor->user); }
                }
            }

            // Deduplicate and initialize Read States (excluding sender)
            $uniqueRecipients = $recipients->unique('id')->reject(fn($u) => $u->id === $sender->id);
            
            // Deduplicate and initialize Read States (excluding sender)
            $uniqueRecipients = $recipients->unique('id')->reject(fn($u) => $u->id === $sender->id);
            
            DB::afterCommit(function() use ($uniqueRecipients, $message) {
                // Pre-load required institutional relationships for notifications
                $message->load(['sender', 'thesis']);
                
                foreach ($uniqueRecipients as $recipient) {
                    try {
                        \App\Models\MessageReadState::firstOrCreate([
                            'message_id' => $message->id,
                            'user_id' => $recipient->id
                        ]);
                        
                        // Safety: Dispatch institutional notification for scholars/staff
                        $recipient->notify(new \App\Notifications\NewMessage($message));
                    } catch (\Exception $e) {
                         // Institutional redundancy: Silent fail for notification errors, ensuring chat continuity.
                         \Illuminate\Support\Facades\Log::warning("Institutional Communication Alert: Failed to notify scholar {$recipient->email} about message {$message->id}. Error: {$e->getMessage()}");
                    }
                }
            });

            return $message;
        });
    }

    /**
     * Mark institutional communications as read for a specific scholar.
     */
    public function markAsRead(ThesisProject $thesis, User $reader)
    {
        return \App\Models\MessageReadState::whereHas('message', function($q) use ($thesis) {
                $q->where('thesis_project_id', $thesis->id);
            })
            ->where('user_id', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
