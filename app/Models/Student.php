<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }
    public function programme() { return $this->belongsTo(Programme::class); }
    public function degree() { return $this->belongsTo(Degree::class); }
    public function cohort() { return $this->belongsTo(Cohort::class); }
    public function supervisors() { return $this->belongsToMany(Supervisor::class, 'student_supervisor')->withPivot('role', 'status', 'assigned_date'); }
    public function milestones() { return $this->hasMany(StudentMilestone::class); }

    //
}
