<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ActionItem extends Model
{
    /** @use HasFactory<\Database\Factories\ActionItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'feedback_id',
        'thesis_project_id',
        'assigned_to',
        'content',
        'due_date',
        'status'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function feedback()
    {
        return $this->belongsTo(Feedback::class);
    }
    
    public function thesis()
    {
        return $this->belongsTo(ThesisProject::class);
    }
    
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
