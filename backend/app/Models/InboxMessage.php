<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InboxMessage extends Model
{
    use HasUuids;

    protected $fillable = [
        'sender_id',
        'subject',
        'body',
        'delivery_method',
        'archived_by_sender',
    ];

    protected $casts = [
        'archived_by_sender' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'inbox_message_recipients')
                    ->withPivot(['id', 'recipient_type', 'read_at', 'is_starred', 'is_archived'])
                    ->withTimestamps();
    }

    public function attachments()
    {
        return $this->hasMany(InboxAttachment::class, 'inbox_message_id');
    }

    // Scopes
    public function scopeInboxFor($query, $userId)
    {
        return $query->whereHas('recipients', function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->where('is_archived', false);
        })->where('delivery_method', '!=', 'email');
    }

    public function scopeSentBy($query, $userId)
    {
        return $query->where('sender_id', $userId)
                     ->where('archived_by_sender', false);
    }
}
