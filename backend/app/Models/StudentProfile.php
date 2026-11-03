<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentProfile extends Model
{
    /** @use HasFactory<\Database\Factories\StudentProfileFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'program_id',
        'level_id',
        'cohort_id',
        'student_id_number',
        'gender',
        'phone_number',
        'nationality',
        'place_of_work',
        'enrollment_status',
        'current_semester'
    ];

    public static function resolveLevelIdFromProgram(?string $programId): ?string
    {
        if (empty($programId)) {
            return null;
        }

        $levels = Level::all();
        if ($levels->isEmpty()) {
            return null;
        }

        $program = Program::find($programId);
        if ($program) {
            $pName = strtolower(($program->code ?? '') . ' ' . ($program->name ?? '') . ' ' . ($program->degree_type ?? ''));
            $isPhd = str_contains($pName, 'phd') || str_contains($pName, 'ph.d') || str_contains($pName, 'doctor');
            $isPgd = str_contains($pName, 'pgd') || str_contains($pName, 'diploma');

            $matched = $levels->first(function ($lvl) use ($isPhd, $isPgd) {
                $lName = strtolower($lvl->name);
                if ($isPhd) {
                    return str_contains($lName, 'phd') || str_contains($lName, 'doctor');
                }
                if ($isPgd) {
                    return str_contains($lName, 'pgd') || str_contains($lName, 'diploma');
                }
                return str_contains($lName, 'msc') || str_contains($lName, 'master');
            });

            if ($matched) {
                return $matched->id;
            }
        }

        return $levels->first()?->id;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function getLevelAttribute()
    {
        if ($this->relationLoaded('level') && $this->getRelation('level') !== null) {
            return $this->getRelation('level');
        }

        if (!empty($this->attributes['level_id'] ?? null)) {
            $lvl = Level::find($this->attributes['level_id']);
            if ($lvl) {
                $this->setRelation('level', $lvl);
                return $lvl;
            }
        }

        if (!empty($this->attributes['program_id'] ?? null)) {
            $resolvedId = static::resolveLevelIdFromProgram($this->attributes['program_id']);
            if ($resolvedId) {
                $lvl = Level::find($resolvedId);
                if ($lvl) {
                    try {
                        \Illuminate\Support\Facades\DB::table('student_profiles')
                            ->where('id', $this->id)
                            ->update(['level_id' => $resolvedId]);
                        $this->attributes['level_id'] = $resolvedId;
                    } catch (\Throwable $e) {}
                    $this->setRelation('level', $lvl);
                    return $lvl;
                }
            }
        }

        return null;
    }

    /**
     * Scope query to students accessible by the given coordinator.
     */
    public function scopeForCoordinator($query, User $user)
    {
        if ($user->hasRole('Director') || $user->hasRole('Admin')) {
            return $query; // View all
        }

        if ($user->hasRole('Program Coordinator')) {
            $scopes = $user->coordinatorScopes();
            if ($scopes->isEmpty()) {
                return $query->whereRaw('0 = 1');
            }

            $hasLevelFilter = $scopes->contains(fn($s) => !empty($s->level_id));
            if (!$hasLevelFilter) {
                return $query->whereIn('program_id', $scopes->pluck('program_id')->unique());
            }
            
            return $query->where(function ($q) use ($scopes) {
                foreach ($scopes as $scope) {
                    $q->orWhere(function ($sub) use ($scope) {
                        $sub->where('program_id', $scope->program_id);
                        if ($scope->level_id) {
                            $sub->where(function ($final) use ($scope) {
                                $final->where('level_id', $scope->level_id)
                                      ->orWhereNull('level_id');
                            });
                        }
                    });
                }
            });
        }

        return $query->whereRaw('0 = 1'); // Deny others by default in this scope
    }
    
    public function thesis()
    {
        return $this->hasOne(ThesisProject::class, 'student_profile_id');
    }

    public function isSeminarCourseLevel()
    {
        $levelName = strtolower($this->level->name ?? '');
        $programName = strtolower($this->program->name ?? '');
        $degreeType = strtolower($this->program->degree_type ?? '');

        return str_contains($levelName, 'seminar') || 
               str_contains($programName, 'seminar') || 
               str_contains($degreeType, 'seminar');
    }

    public function isPhd(): bool
    {
        $pName = strtolower(($this->program?->name ?? '') . ' ' . ($this->program?->degree_type ?? ''));
        $lName = strtolower($this->level?->name ?? '');

        return str_contains($pName, 'phd') 
            || str_contains($pName, 'ph.d') 
            || str_contains($pName, 'doctor') 
            || str_contains($lName, 'phd') 
            || str_contains($lName, 'doctor');
    }

    public function requiredPublicationsCount(): int
    {
        return $this->isPhd() ? 2 : 1;
    }

    public function isPhdAI(): bool
    {
        $pName = strtolower(($this->program?->name ?? '') . ' ' . ($this->program?->degree_type ?? ''));
        $lName = strtolower($this->level?->name ?? '');

        $isPhd = $this->isPhd();

        $isAI = str_contains($pName, 'artificial intelligence') 
            || str_contains($pName, 'intelligence') 
            || str_contains($pName, ' ai') 
            || str_ends_with($pName, 'ai') 
            || ($this->program?->code && strtoupper($this->program->code) === 'AI');

        return $isPhd && $isAI;
    }

    /**
     * Resolve the discipline group (Cybersecurity, Artificial Intelligence, Management Information Systems)
     */
    public function getDisciplineGroupAttribute(): string
    {
        $pName = strtolower(($this->program?->name ?? '') . ' ' . ($this->program?->code ?? ''));
        if (str_contains($pName, 'cyber')) {
            return 'cybersecurity';
        }
        if (str_contains($pName, 'intelligence') || str_contains($pName, ' ai') || str_ends_with($pName, 'ai')) {
            return 'artificial_intelligence';
        }
        if (str_contains($pName, 'management') || str_contains($pName, 'information') || str_contains($pName, 'mis')) {
            return 'management_information_systems';
        }
        return 'other';
    }

    public function getDisciplineGroupLabelAttribute(): string
    {
        return match($this->discipline_group) {
            'cybersecurity' => 'Cybersecurity',
            'artificial_intelligence' => 'Artificial Intelligence',
            'management_information_systems' => 'Management Information Systems',
            default => 'General',
        };
    }

    /**
     * Handle cascade deletions.
     */
    protected static function booted()
    {
        static::saving(function ($student) {
            if (empty($student->level_id) && !empty($student->program_id)) {
                $student->level_id = static::resolveLevelIdFromProgram($student->program_id);
            }
        });

        static::deleting(function ($studentProfile) {
            // Delete associated thesis project (triggers further cascade)
            if ($studentProfile->thesis) {
                $studentProfile->thesis->delete();
            }

            // Delete the user account associated with the student
            // Use withoutEvents to avoid accidental loops if user was deleting student
            if ($studentProfile->user) {
                $studentProfile->user->delete();
            }
        });
    }
}
