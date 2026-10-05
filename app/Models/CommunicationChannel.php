<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationChannel extends Model
{
    use HasUuids;

    protected $fillable = [
        'thesis_project_id',
        'type',
        'created_by',
    ];

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(ThesisProject::class, 'thesis_project_id');
    }

    public function thesisProject(): BelongsTo
    {
        return $this->thesis();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'channel_id');
    }

    /**
     * Handle cascade deletions.
     */
    protected static function booted()
    {
        static::deleting(function ($channel) {
            // Delete messages
            $channel->messages()->delete();
        });
    }
}
