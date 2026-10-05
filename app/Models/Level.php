<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Level extends Model
{
    /** @use HasFactory<\Database\Factories\LevelFactory> */
    use HasFactory, HasUuids;

    protected $fillable = ['name'];

    public function studentProfiles()
    {
        return $this->hasMany(StudentProfile::class);
    }
    
    public function coordinatorProfiles()
    {
        return $this->hasMany(CoordinatorProfile::class);
    }
}
