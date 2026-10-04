<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Evaluation extends Model
{
    /** @use HasFactory<\Database\Factories\EvaluationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'defence_event_id',
        'evaluator_id',
        'score',
        'recommendation',
        'comments',
        'submitted_at'
    ];

    protected $casts = [
        'score' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(DefenceEvent::class, 'defence_event_id');
    }

    public function defenceEvent()
    {
        return $this->belongsTo(DefenceEvent::class, 'defence_event_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
