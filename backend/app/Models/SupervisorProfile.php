<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupervisorProfile extends Model
{
    /** @use HasFactory<\Database\Factories\SupervisorProfileFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'staff_id',
        'max_students',
        'current_load',
        'specialization',
        'rank'
    ];

    protected static function booted()
    {
        static::creating(function ($profile) {
            if (empty($profile->staff_id)) {
                $profile->staff_id = 'STF-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'program_supervisor');
    }
    
    public function assignments()
    {
        return $this->hasMany(SupervisionAssignment::class, 'supervisor_profile_id');
    }

    public function getDepartmentAttribute()
    {
        return $this->programs->first()?->department ?? null;
    }
}
