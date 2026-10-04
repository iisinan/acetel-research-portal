<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Submission extends Model
{
    /** @use HasFactory<\Database\Factories\SubmissionFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_milestone_id',
        'type',
        'version',
        'file_url',
        'file_meta',
        'checksum',
        'submitted_by',
        'description',
        'plagiarism_data'
    ];

    protected $casts = [
        'file_meta' => 'array',
        'plagiarism_data' => 'array',
    ];

    public function milestone()
    {
        return $this->belongsTo(StudentMilestone::class, 'student_milestone_id');
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }
    
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Handle cleanup.
     */
    protected static function booted()
    {
        static::saved(function ($submission) {
            if ($submission->milestone?->thesis) {
                (new \App\Services\MilestoneWorkflowService())->tryAutoAdvanceSupervisorsAssigned($submission->milestone->thesis);
            }
        });

        static::deleting(function ($submission) {
            if ($submission->file_url) {
                \Illuminate\Support\Facades\Storage::delete($submission->file_url);
            }
        });
    }
}
