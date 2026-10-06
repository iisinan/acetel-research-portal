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

    /**
     * Handle cascade deletions.
     */
    protected static function booted()
    {
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
