<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Message extends Model
{
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'channel_id',
        'thesis_project_id',
        'student_milestone_id',
        'user_id',
        'reply_to_id',
        'content',
        'type',
        'file_path',
        'meta',
        'read_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(CommunicationChannel::class, 'channel_id');
    }

    public function thesis()
    {
        return $this->belongsTo(ThesisProject::class);
    }

    public function milestone()
    {
        return $this->belongsTo(StudentMilestone::class, 'student_milestone_id');
    }
    
    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->sender();
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }
}
