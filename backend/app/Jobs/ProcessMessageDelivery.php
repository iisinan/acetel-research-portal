<?php

namespace App\Jobs;

use App\Models\InboxMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class ProcessMessageDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;
    public $recipientIds;

    /**
     * Create a new job instance.
     */
    public function __construct(InboxMessage $message, array $recipientIds)
    {
        $this->message = $message;
        $this->recipientIds = $recipientIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->recipientIds)) {
            return;
        }

        // Chunk broadcasts to avoid memory issues
        if (in_array($this->message->delivery_method, ['in_app', 'both', null])) {
            foreach ($this->recipientIds as $userId) {
                \App\Events\MessageReceived::dispatch($this->message, $userId);
            }
        }

        // Chunk notifications
        if (in_array($this->message->delivery_method, ['email', 'both', null])) {
            $userChunks = array_chunk($this->recipientIds, 500);
            foreach ($userChunks as $chunk) {
                $users = User::whereIn('id', $chunk)->get();
                Notification::send($users, new \App\Notifications\NewInboxMessage($this->message));
            }
        }
    }
}
