<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cohort extends Model
{
    /** @use HasFactory<\Database\Factories\CohortFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', 
        'code',
        'intake_year',
        'status',
        'created_by',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'intake_year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date'
    ];



    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function students()
    {
        return $this->hasMany(StudentProfile::class);
    }

    /**
     * Handle cascade deletions.
     */
    protected static function booted()
    {
        static::deleting(function ($cohort) {
            $cohort->students->each(function ($student) {
                // We delete the student profile which will trigger further cascades
                $student->delete();
            });
        });
    }
}
