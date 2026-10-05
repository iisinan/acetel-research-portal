<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PanelMember extends Model
{
    /** @use HasFactory<\Database\Factories\PanelMemberFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'defence_event_id',
        'user_id',
        'role',
        'invitation_status',
        'is_present',
    ];

    protected $casts = [
        'is_present' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(DefenceEvent::class, 'defence_event_id');
    }

    public function defenceEvent()
    {
        return $this->belongsTo(DefenceEvent::class, 'defence_event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
