<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MilestoneTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\MilestoneTemplateFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'program_id',
        'name',
        'slug',
        'order',
        'requires_submission',
        'submission_requires_approval',
        'submission_approver_roles',
        'requires_approval',
        'has_chat',
        'show_supervisor_details',
        'required_approvers',
        'approval_threshold',
        'description',
        'metadata',
        'submission_type',
        'is_final_archival',
        'allow_defence_date',
        'defence_type',
        'global_defence_date',
        'defence_date_role',
        'show_supervisor_assignment',
        'show_internal_examiner_assignment',
        'show_external_examiner_assignment',
        'allow_plagiarism_report',
        'plagiarism_report_role',
    ];

    protected $casts = [
        'required_approvers' => 'array',
        'submission_approver_roles' => 'array',
        'requires_submission' => 'boolean',
        'submission_requires_approval' => 'boolean',
        'requires_approval' => 'boolean',
        'approval_threshold' => 'integer',
        'has_chat' => 'boolean',
        'show_supervisor_details' => 'boolean',
        'metadata' => 'array',
        'submission_type' => 'array',
        'is_final_archival' => 'boolean',
        'allow_defence_date' => 'boolean',
        'global_defence_date' => 'date',
        'allow_plagiarism_report' => 'boolean',
        'show_supervisor_assignment' => 'boolean',
        'show_internal_examiner_assignment' => 'boolean',
        'show_external_examiner_assignment' => 'boolean',
    ];

    public function getSubmissionTypeAttribute($value)
    {
        return json_decode($value ?? '[]', true) ?: ['file'];
    }

    protected static function boot()
    {
        parent::boot();

        // When creating a new template, shift existing ones at or above this order
        static::creating(function (MilestoneTemplate $template) {
            // If no order specified, append to end
            if (empty($template->order)) {
                $template->order = static::where(function ($q) use ($template) {
                    $q->where('program_id', $template->program_id)
                      ->orWhereNull('program_id');
                })->max('order') + 1;
            } else {
                // Shift existing templates at or above this order up by 1
                static::where(function ($q) use ($template) {
                    $q->where('program_id', $template->program_id)
                      ->orWhereNull('program_id');
                })
                ->where('order', '>=', $template->order)
                ->increment('order');
            }
        });

        // When updating, handle the "smart" shifting logic
        static::updating(function (MilestoneTemplate $template) {
            if ($template->isDirty('order')) {
                $oldOrder = $template->getOriginal('order');
                $newOrder = $template->order;
                $programId = $template->program_id;

                $query = static::where(function ($q) use ($programId) {
                    $q->where('program_id', $programId);
                    if ($programId === null) {
                        $q->orWhereNull('program_id');
                    }
                })->where('id', '!=', $template->id);

                if ($newOrder < $oldOrder) {
                    // Moving up: increment roles between [new, old-1]
                    (clone $query)->whereBetween('order', [$newOrder, $oldOrder - 1])->increment('order');
                } else {
                    // Moving down: decrement roles between [old+1, new]
                    (clone $query)->whereBetween('order', [$oldOrder + 1, $newOrder])->decrement('order');
                }
            }
        });

        // After deletion, renumber all remaining templates sequentially
        static::deleted(function (MilestoneTemplate $template) {
            static::renumberSequence($template->program_id);
        });
    }

    /**
     * Renumber all templates sequentially (1, 2, 3...) for a given program scope.
     */
    public static function renumberSequence(?string $programId = null): void
    {
        $templates = static::where(function ($q) use ($programId) {
            $q->where('program_id', $programId)
              ->orWhereNull('program_id');
        })->orderBy('order')->get();

        foreach ($templates as $index => $template) {
            $newOrder = $index + 1;
            if ($template->order !== $newOrder) {
                $template->update(['order' => $newOrder]);
            }
        }
    }

    public function studentMilestones()
    {
        return $this->hasMany(StudentMilestone::class, 'milestone_template_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get active presentation schedules across templates that have not yet passed.
     */
    public static function getActivePresentations()
    {
        $today = now()->toDateString();

        return static::where('slug', '!=', 'supervisors_assigned')
            ->where('order', '!=', 2)
            ->whereHas('studentMilestones', function ($q) use ($today) {
                $q->whereNotNull('defence_date')
                  ->whereDate('defence_date', '>=', $today)
                  ->where('status', '!=', 'approved');
            })
            ->with(['studentMilestones' => function ($q) use ($today) {
                $q->whereNotNull('defence_date')
                  ->whereDate('defence_date', '>=', $today)
                  ->where('status', '!=', 'approved');
            }])
            ->orderBy('order')
            ->get();
    }

    /**
     * Presentation display title for sidebar and tabs.
     */
    public function getPresentationTitleAttribute(): string
    {
        $name = trim($this->name);
        if (str_ends_with(strtolower($name), 'presentation')) {
            return $name;
        }
        return $name . ' Presentation';
    }
}
