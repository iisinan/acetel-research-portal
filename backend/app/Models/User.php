<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locked_at',
        'is_active',
        'last_login_at',
        'creator_id',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function supervisorProfile()
    {
        return $this->hasOne(SupervisorProfile::class);
    }

    public function coordinatorProfiles()
    {
        return $this->hasMany(CoordinatorProfile::class);
    }

    public function internalExaminerProfiles()
    {
        return $this->hasMany(InternalExaminerProfile::class);
    }

    public function externalExaminerProfiles()
    {
        return $this->hasMany(ExternalExaminerProfile::class);
    }

    public function getInternalExaminerProfileAttribute()
    {
        return $this->internalExaminerProfiles()->first();
    }

    public function getExternalExaminerProfileAttribute()
    {
        return $this->externalExaminerProfiles()->first();
    }

    /**
     * Get the program and level scopes for a coordinator.
     */
    public function coordinatorScopes()
    {
        // 1. Institutional leadership (Admin & Director) oversees all programs and levels
        if ($this->hasAnyRole(['Admin', 'Director'])) {
            $allPrograms = \App\Models\Program::all();
            return $allPrograms->map(function ($program) {
                return (object)[
                    'program_id' => $program->id,
                    'level_id' => null,
                ];
            });
        }

        if (!$this->hasRole('Program Coordinator')) {
            return collect();
        }

        // 2. Auto-heal/provision coordinator profiles if missing or inactive
        $this->ensureCoordinatorProfiles();

        return $this->coordinatorProfiles()
            ->where('active', true)
            ->get(['program_id', 'level_id']);
    }

    /**
     * Auto-heal and provision CoordinatorProfile records if missing or inactive.
     */
    public function ensureCoordinatorProfiles(): void
    {
        if (!$this->hasRole('Program Coordinator')) {
            return;
        }

        // If active coordinator profiles already exist, nothing to do
        if ($this->coordinatorProfiles()->where('active', true)->exists()) {
            return;
        }

        // If inactive coordinator profiles exist, activate them
        if ($this->coordinatorProfiles()->exists()) {
            $this->coordinatorProfiles()->update(['active' => true]);
            return;
        }

        // Fallback 1: Link programs from user's supervisor profile if present
        $supervisorPrograms = $this->supervisorProfile?->programs;
        if ($supervisorPrograms && $supervisorPrograms->isNotEmpty()) {
            $levels = \App\Models\Level::all();
            foreach ($supervisorPrograms as $prog) {
                if ($levels->isNotEmpty()) {
                    foreach ($levels as $lvl) {
                        \App\Models\CoordinatorProfile::firstOrCreate([
                            'user_id' => $this->id,
                            'program_id' => $prog->id,
                            'level_id' => $lvl->id,
                        ], ['active' => true]);
                    }
                } else {
                    \App\Models\CoordinatorProfile::firstOrCreate([
                        'user_id' => $this->id,
                        'program_id' => $prog->id,
                        'level_id' => null,
                    ], ['active' => true]);
                }
            }
            return;
        }

        // Fallback 2: Link all existing programs in the system
        $allPrograms = \App\Models\Program::all();
        $levels = \App\Models\Level::all();
        foreach ($allPrograms as $prog) {
            if ($levels->isNotEmpty()) {
                foreach ($levels as $lvl) {
                    \App\Models\CoordinatorProfile::firstOrCreate([
                        'user_id' => $this->id,
                        'program_id' => $prog->id,
                        'level_id' => $lvl->id,
                    ], ['active' => true]);
                }
            } else {
                \App\Models\CoordinatorProfile::firstOrCreate([
                    'user_id' => $this->id,
                    'program_id' => $prog->id,
                    'level_id' => null,
                ], ['active' => true]);
            }
        }
    }

    /**
     * Check if coordinator has access to a student.
     */
    public function hasCoordinatorAccess($student)
    {
        if ($this->hasAnyRole(['Admin', 'Director'])) {
            return true;
        }

        if (!$this->hasRole('Program Coordinator')) {
            return false;
        }

        $scopes = $this->coordinatorScopes();
        if ($scopes->isEmpty()) {
            return false;
        }

        return $scopes->contains(function ($scope) use ($student) {
            $matchesProgram = ($scope->program_id == $student->program_id);
            if (!$matchesProgram) {
                return false;
            }
            if (!empty($scope->level_id) && !empty($student->level_id)) {
                return $scope->level_id == $student->level_id;
            }
            return true;
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'creator_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Institutional unread communication telemetry.
     */
    public function getUnreadMessagesCountAttribute()
    {
        return \App\Models\MessageReadState::where('user_id', $this->id)
            ->whereNull('read_at')
            ->count();
    }

    public function firstName()
    {
        return explode(' ', $this->name)[0] ?? $this->name;
    }

    protected static function booted()
    {
        static::deleting(function ($user) {
            // Handle relations that don't have DB-level cascade deletes
            $user->auditLogs()->delete();
            $user->messages()->delete();
        });
    }
}
