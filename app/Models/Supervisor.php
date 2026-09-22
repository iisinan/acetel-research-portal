<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }
    public function students() { return $this->belongsToMany(Student::class, 'student_supervisor')->withPivot('role', 'status', 'assigned_date'); }
    public function allocations() { return $this->hasMany(SupervisorAllocation::class); }

    //
}
