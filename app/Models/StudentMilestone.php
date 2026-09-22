<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMilestone extends Model
{
    protected $guarded = [];

    public function milestone() { return $this->belongsTo(Milestone::class); }
    public function student() { return $this->belongsTo(Student::class); }
    public function documents() { return $this->hasMany(MilestoneDocument::class); }

    //
}
