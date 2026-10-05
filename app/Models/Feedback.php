<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Feedback extends Model
{
    /** @use HasFactory<\Database\Factories\FeedbackFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'submission_id',
        'decision',
        'remarks',
        'created_by'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
