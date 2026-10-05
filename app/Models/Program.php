<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Program extends Model
{
    /** @use HasFactory<\Database\Factories\ProgramFactory> */
    use HasFactory, HasUuids;
    
    protected static function booted()
    {
        static::creating(function ($program) {
            if (empty($program->serial_number)) {
                // PostgreSQL compatible numeric ordering
                $lastProgram = static::whereNotNull('serial_number')
                    ->whereRaw("serial_number ~ '^[0-9]+$'") // Ensure numeric only
                    ->orderByRaw('CAST(serial_number AS INTEGER) DESC')
                    ->first();
                    
                $nextNumber = $lastProgram ? (intval($lastProgram->serial_number) + 1) : 1;
                $program->serial_number = (string) $nextNumber;
            }
        });
    }

    protected $fillable = ['name', 'code', 'degree_type', 'serial_number'];

    public function cohorts()
    {
        return $this->hasMany(Cohort::class);
    }
    
    public function coordinatorProfiles()
    {
        return $this->hasMany(CoordinatorProfile::class);
    }

    public function students()
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function supervisors()
    {
        return $this->hasMany(SupervisorProfile::class);
    }
}
