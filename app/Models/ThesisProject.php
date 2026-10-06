<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ThesisProject extends Model
{
    /** @use HasFactory<\Database\Factories\ThesisProjectFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_profile_id',
        'title',
        'abstract',
        'keywords',
        'status',
        'start_date',
        'end_date',
        'cleared_for_internal_at',
        'internal_examiner_profile_id',
        'external_examiner_profile_id',
        'proposed_supervisors',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cleared_for_internal_at' => 'datetime',
        'proposed_supervisors' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($thesis) {
            if (empty($thesis->status)) {
                $thesis->status = 'proposed';
            }
        });

        static::deleting(function ($thesis) {
            // Delete milestones (each will trigger further cascade if needed)
            $thesis->milestones->each->delete();
            
            // Delete assignments
            $thesis->assignments()->delete();
            
            // Delete examiner assignments
            $thesis->examinerAssignments()->delete();
            
            // Delete defence events
            $thesis->defenceEvents->each->delete();
            
            // Delete communication channels
            $thesis->communicationChannels->each->delete();
            
            // Delete direct messages
            $thesis->messages()->delete();
            
            // Delete action items
            $thesis->actionItems()->delete();
        });
    }


    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function internalExaminer()
    {
        return $this->belongsTo(InternalExaminerProfile::class, 'internal_examiner_profile_id');
    }

    public function externalExaminer()
    {
        return $this->belongsTo(ExternalExaminerProfile::class, 'external_examiner_profile_id');
    }

    public function assignments()
    {
        return $this->hasMany(SupervisionAssignment::class);
    }

    /**
     * Alias for assignments to support legacy/admin views.
     */
    public function supervisors()
    {
        return $this->assignments();
    }
    
    public function milestones()
    {
        return $this->hasMany(StudentMilestone::class);
    }

    public function currentMilestone()
    {
        return $this->hasOne(StudentMilestone::class)
            ->whereNotIn('status', ['completed', 'approved'])
            ->join('milestone_templates', 'student_milestones.milestone_template_id', '=', 'milestone_templates.id')
            ->orderBy('milestone_templates.order', 'asc')
            ->select('student_milestones.*');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function communicationChannels()
    {
        return $this->hasMany(CommunicationChannel::class);
    }

    public function examinerAssignments()
    {
        return $this->hasMany(ExaminerAssignment::class);
    }
    
    public function defenceEvents()
    {
        return $this->hasMany(DefenceEvent::class);
    }

    /**
     * Alias for defenceEvents to support legacy/admin views.
     */
    public function defences()
    {
        return $this->defenceEvents();
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class);
    }

    /**
     * Scope for theses that have reached Final Library Archival (Milestone 13 - Approved).
     */
    public function scopePubliclyVisible($query)
    {
        return $query->whereHas('milestones', function ($q) {
            $q->whereHas('template', function ($t) {
                $t->where('is_final_archival', true);
            })->where('status', 'approved');
        });
    }

    /**
     * Scope for theses that have cleared Internal Defence (Milestone 6 - Approved).
     */
    public function scopeAuditableByAdmin($query)
    {
        return $query->whereHas('milestones', function ($q) {
            $q->whereHas('template', function ($t) {
                $t->where('order', '>=', 6);
            })->where('status', 'approved');
        });
    }

    public function isAuditableByAdmin(): bool
    {
        if ($this->relationLoaded('milestones')) {
            return $this->milestones->contains(function ($m) {
                return ($m->template?->order ?? 0) >= 6 && $m->status === 'approved';
            });
        }

        return $this->milestones()
            ->whereHas('template', function ($t) {
                $t->where('order', '>=', 6);
            })->where('status', 'approved')
            ->exists();
    }

    public function isPubliclyVisible(): bool
    {
        if ($this->relationLoaded('milestones')) {
            return $this->milestones->contains(function ($m) {
                return !empty($m->template?->is_final_archival) && $m->status === 'approved';
            });
        }

        return $this->milestones()
            ->whereHas('template', function ($t) { $t->where('is_final_archival', true); })
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Retrieve the final institutional library copy (PDF from Milestone 13).
     */
    public function getLibraryCopyAttribute()
    {
        if ($this->relationLoaded('milestones')) {
            $milestone = $this->milestones->first(fn($m) => !empty($m->template?->is_final_archival));
            if ($milestone) {
                return $milestone->relationLoaded('submissions')
                    ? $milestone->submissions->sortByDesc('created_at')->first()
                    : $milestone->submissions()->latest()->first();
            }
        }

        $milestone = $this->milestones()
            ->whereHas('template', function ($t) { $t->where('is_final_archival', true); })
            ->first();

        return $milestone?->submissions()->latest()->first();
    }

    /**
     * Ensure all required milestones exist for this project.
     */
    public function syncMilestones()
    {
        $templates = MilestoneTemplate::whereNull('program_id')
            ->when($this->student?->program_id, fn($q, $pid) => $q->orWhere('program_id', $pid))
            ->orderBy('order')
            ->get();

        foreach ($templates as $template) {
            StudentMilestone::firstOrCreate(
                [
                    'thesis_project_id' => $this->id,
                    'milestone_template_id' => $template->id
                ],
                ['status' => 'not_started']
            );
        }
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->relationLoaded('milestones')) {
            $total = $this->milestones->count();
            if ($total === 0) return 0;

            $approved = $this->milestones
                ->where('status', 'approved')
                ->count();

            return round(($approved / $total) * 100);
        }

        $total = $this->milestones()->count();
        if ($total === 0) return 0;
        
        $approved = $this->milestones()
            ->where('status', 'approved')
            ->count();
            
        return round(($approved / $total) * 100);
    }
}


